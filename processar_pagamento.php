<?php
set_time_limit(60);
ini_set('default_socket_timeout', 60);
ini_set('memory_limit', '256M');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/log_pagamento.txt');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=UTF-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/vendor/autoload.php';
include_once "conexao.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $message, int $statusCode = 400): void
{
    json_response([
        'status' => 'error',
        'message' => $message,
    ], $statusCode);
}

function resolver_modo_pagamento(): string
{
    $modo = strtolower((string)(getenv('APP_PAYMENT_MODE') ?: 'sandbox'));
    $modosValidos = ['sandbox', 'production', 'test_simulated'];
    return in_array($modo, $modosValidos, true) ? $modo : 'sandbox';
}

function resolver_access_token(string $modo): string
{
    $tokenGenerico = trim((string)(getenv('MP_ACCESS_TOKEN') ?: ''));
    $tokenTeste = trim((string)(getenv('MP_ACCESS_TOKEN_TEST') ?: ''));
    $tokenProd = trim((string)(getenv('MP_ACCESS_TOKEN_PROD') ?: ''));

    if ($modo === 'production') {
        if ($tokenProd !== '' && stripos($tokenProd, 'TEST-') !== 0) {
            return $tokenProd;
        }
        if ($tokenGenerico !== '' && stripos($tokenGenerico, 'TEST-') !== 0) {
            return $tokenGenerico;
        }
        return '';
    }

    if ($modo === 'sandbox') {
        // Em sandbox, aceitamos apenas token de teste para evitar uso acidental de token live.
        if ($tokenTeste !== '' && stripos($tokenTeste, 'TEST-') === 0) {
            return $tokenTeste;
        }
        if ($tokenGenerico !== '' && stripos($tokenGenerico, 'TEST-') === 0) {
            return $tokenGenerico;
        }
        return '';
    }

    return '';
}

function calcular_totais_carrinho(mysqli $conn, array $carrinho, string $estadoDestino): array
{
    $subtotal = 0.0;
    $pesoTotal = 0.0;

    $stmt = $conn->prepare("SELECT preco_venda_peca, peso_peca FROM tbl_pecas WHERE id_peca = ?");

    foreach ($carrinho as $idPeca => $qtdItem) {
        $idPeca = (int)$idPeca;
        $qtdItem = (int)$qtdItem;
        if ($idPeca <= 0 || $qtdItem <= 0) {
            continue;
        }

        $stmt->bind_param('i', $idPeca);
        $stmt->execute();
        $res = $stmt->get_result();
        $peca = $res->fetch_assoc();

        if (!$peca) {
            throw new RuntimeException("Peca #{$idPeca} nao encontrada.");
        }

        $preco = (float)$peca['preco_venda_peca'];
        $peso = (float)$peca['peso_peca'];
        if ($preco <= 0) {
            continue;
        }

        $subtotal += $preco * $qtdItem;
        $pesoTotal += $peso * $qtdItem;
    }

    $stmt->close();

    if ($subtotal <= 0) {
        throw new RuntimeException('Carrinho invalido ou vazio.');
    }

    $frete = 0.0;
    if ($pesoTotal > 0 && $estadoDestino !== '') {
        if ($estadoDestino === 'SP') {
            if ($pesoTotal <= 5) {
                $frete = 15.0;
            } elseif ($pesoTotal <= 20) {
                $frete = 45.0;
            } else {
                $frete = 90.0;
            }
        } else {
            if ($pesoTotal <= 5) {
                $frete = 35.0;
            } else {
                $frete = 120.0;
            }
        }
    }

    return [
        'subtotal' => $subtotal,
        'frete' => $frete,
        'total' => $subtotal + $frete,
    ];
}

function resolver_endereco_entrega(mysqli $conn, int $idCliente, ?array $entregaTemporaria): array
{
    $usarCadastro = true;
    if (is_array($entregaTemporaria) && array_key_exists('usar_cadastro', $entregaTemporaria)) {
        $flag = $entregaTemporaria['usar_cadastro'];
        $usarCadastro = ($flag === true || $flag === 'true' || $flag === 1 || $flag === '1');
    }

    if ($usarCadastro) {
        $stmt = $conn->prepare(
            "SELECT cep_cliente, endereco_cliente, numero_cliente, complemento_cliente, bairro_cliente, cidade_cliente, uf_cliente
             FROM tbl_clientes
             WHERE id_cliente = ?"
        );
        $stmt->bind_param('i', $idCliente);
        $stmt->execute();
        $res = $stmt->get_result();
        $cliente = $res->fetch_assoc();
        $stmt->close();

        if (!$cliente) {
            throw new RuntimeException('Cliente nao encontrado para entrega.');
        }

        return [
            'cep' => (string)$cliente['cep_cliente'],
            'logradouro' => (string)$cliente['endereco_cliente'],
            'numero' => (string)$cliente['numero_cliente'],
            'complemento' => (string)$cliente['complemento_cliente'],
            'bairro' => (string)$cliente['bairro_cliente'],
            'cidade' => (string)$cliente['cidade_cliente'],
            'uf' => strtoupper((string)$cliente['uf_cliente']),
        ];
    }

    $ent = is_array($entregaTemporaria) ? $entregaTemporaria : [];
    return [
        'cep' => trim((string)($ent['cep'] ?? '')),
        'logradouro' => trim((string)($ent['logradouro'] ?? '')),
        'numero' => trim((string)($ent['numero'] ?? '')),
        'complemento' => trim((string)($ent['complemento'] ?? '')),
        'bairro' => trim((string)($ent['bairro'] ?? '')),
        'cidade' => trim((string)($ent['cidade'] ?? '')),
        'uf' => strtoupper(trim((string)($ent['uf'] ?? ''))),
    ];
}

function criar_pagamento_mercadopago(array $data, float $valorTotal, int $idCliente): array
{
    $modo = resolver_modo_pagamento();
    if ($modo === 'test_simulated') {
        return [
            'id' => 'SIM-' . time(),
            'status' => 'approved',
            'status_detail' => 'simulated',
        ];
    }

    $accessToken = resolver_access_token($modo);
    if ($accessToken === '') {
        throw new RuntimeException(
            $modo === 'production'
                ? 'MP_ACCESS_TOKEN_PROD/MP_ACCESS_TOKEN nao configurado.'
                : 'MP_ACCESS_TOKEN_TEST/MP_ACCESS_TOKEN nao configurado.'
        );
    }

    MercadoPagoConfig::setAccessToken($accessToken);
    if ($modo === 'sandbox') {
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    } else {
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);
    }

    $token = trim((string)($data['token'] ?? ''));
    $paymentMethodId = trim((string)($data['payment_method_id'] ?? ''));
    $installments = max(1, (int)($data['installments'] ?? 1));
    $issuerIdRaw = $data['issuer_id'] ?? null;
    $issuerId = ($issuerIdRaw !== null && $issuerIdRaw !== '') ? (int)$issuerIdRaw : null;

    $payer = is_array($data['payer'] ?? null) ? $data['payer'] : [];
    $payerEmail = trim((string)($payer['email'] ?? ($_SESSION['cliente_email'] ?? '')));
    $docType = strtoupper(trim((string)($payer['identification']['type'] ?? '')));
    $docNumber = preg_replace('/\D+/', '', (string)($payer['identification']['number'] ?? ''));

    if ($token === '' || $paymentMethodId === '') {
        throw new RuntimeException('Dados do cartao incompletos (token/metodo).');
    }
    if (!filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Email do pagador invalido.');
    }

    $payerRequest = ['email' => $payerEmail];
    if ($docType !== '' && $docNumber !== '') {
        $payerRequest['identification'] = [
            'type' => $docType,
            'number' => $docNumber,
        ];
    }

    $request = [
        'transaction_amount' => round($valorTotal, 2),
        'token' => $token,
        'description' => 'Compra Boxter Auto Pecas',
        'installments' => $installments,
        'payment_method_id' => $paymentMethodId,
        'payer' => $payerRequest,
        'external_reference' => 'boxter_cli_' . $idCliente . '_' . time(),
        'binary_mode' => false,
    ];

    if ($issuerId !== null && $issuerId > 0) {
        $request['issuer_id'] = $issuerId;
    }

    $requestOptions = new RequestOptions();
    $idempotencyKey = hash('sha256', $idCliente . '|' . $token . '|' . $valorTotal . '|' . $installments);
    $requestOptions->setCustomHeaders(["X-Idempotency-Key: {$idempotencyKey}"]);

    $client = new PaymentClient();

    try {
        $payment = $client->create($request, $requestOptions);
        return [
            'id' => (string)$payment->id,
            'status' => strtolower((string)$payment->status),
            'status_detail' => (string)$payment->status_detail,
        ];
    } catch (MPApiException $e) {
        $api = $e->getApiResponse();
        $body = $api ? $api->getContent() : null;
        $mensagem = 'Falha ao processar pagamento no Mercado Pago.';
        if (is_array($body) && isset($body['message'])) {
            $mensagem = (string)$body['message'];
        }
        throw new RuntimeException($mensagem);
    }
}

if (empty($_SESSION['cliente_id'])) {
    json_error('Sessao expirada. Faca login novamente.', 401);
}

if (empty($_SESSION['carrinho']) || !is_array($_SESSION['carrinho'])) {
    json_error('Carrinho vazio para pagamento.', 422);
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);
if (!is_array($data)) {
    json_error('Payload invalido.', 400);
}

$idCliente = (int)$_SESSION['cliente_id'];
$estadoDestino = strtoupper(trim((string)($_SESSION['entrega_uf'] ?? ($_SESSION['cliente_estado'] ?? ''))));

$pedidoId = 0;
$transacaoAberta = false;

try {
    $totais = calcular_totais_carrinho($conn, $_SESSION['carrinho'], $estadoDestino);
    $valorTotal = (float)$totais['total'];

    $valorInformado = (float)($data['transaction_amount'] ?? 0);
    if ($valorInformado > 0 && abs($valorInformado - $valorTotal) > 0.5) {
        throw new RuntimeException('Valor do pagamento divergente do total do carrinho.');
    }

    $pagamento = criar_pagamento_mercadopago($data, $valorTotal, $idCliente);
    $statusMp = $pagamento['status'];
    $idTransacaoMp = $pagamento['id'];
    $statusDetail = $pagamento['status_detail'];

    $statusValidosPedido = ['approved', 'pending', 'in_process'];
    if (!in_array($statusMp, $statusValidosPedido, true)) {
        json_response([
            'status' => $statusMp,
            'id' => $idTransacaoMp,
            'pedido_id' => 0,
            'mode' => resolver_modo_pagamento(),
            'message' => 'Pagamento nao aprovado.',
            'status_detail' => $statusDetail,
        ], 200);
    }

    $entrega = resolver_endereco_entrega($conn, $idCliente, $_SESSION['entrega_temporaria'] ?? null);

    $conn->begin_transaction();
    $transacaoAberta = true;

    $stmtPedido = $conn->prepare(
        "INSERT INTO tbl_pedido (
            id_cliente,
            data_pedido,
            status_pedido,
            valor_total_pedido,
            entrega_cep,
            entrega_logradouro,
            entrega_numero,
            entrega_complemento,
            entrega_bairro,
            entrega_cidade,
            entrega_uf
        ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmtPedido->bind_param(
        'isdsssssss',
        $idCliente,
        $statusMp,
        $valorTotal,
        $entrega['cep'],
        $entrega['logradouro'],
        $entrega['numero'],
        $entrega['complemento'],
        $entrega['bairro'],
        $entrega['cidade'],
        $entrega['uf']
    );
    $stmtPedido->execute();
    $pedidoId = (int)$conn->insert_id;
    $stmtPedido->close();

    $stmtPeca = $conn->prepare("SELECT preco_venda_peca, estoque_peca FROM tbl_pecas WHERE id_peca = ? FOR UPDATE");
    $stmtItem = $conn->prepare("INSERT INTO tbl_itens_pedido (id_pedido, id_peca, qtde_item, preco_unit_item) VALUES (?, ?, ?, ?)");
    $stmtEstoque = $conn->prepare("UPDATE tbl_pecas SET estoque_peca = estoque_peca - ? WHERE id_peca = ?");

    foreach ($_SESSION['carrinho'] as $idPeca => $qtdItem) {
        $idPeca = (int)$idPeca;
        $qtdItem = (int)$qtdItem;
        if ($idPeca <= 0 || $qtdItem <= 0) {
            continue;
        }

        $stmtPeca->bind_param('i', $idPeca);
        $stmtPeca->execute();
        $resPeca = $stmtPeca->get_result();
        $peca = $resPeca->fetch_assoc();

        if (!$peca) {
            throw new RuntimeException("Peca #{$idPeca} nao encontrada.");
        }

        $precoUnit = (float)$peca['preco_venda_peca'];
        $estoqueAtual = (int)$peca['estoque_peca'];
        if ($qtdItem > $estoqueAtual) {
            throw new RuntimeException("Estoque insuficiente para a peca #{$idPeca}.");
        }

        $stmtItem->bind_param('iiid', $pedidoId, $idPeca, $qtdItem, $precoUnit);
        $stmtItem->execute();

        $stmtEstoque->bind_param('ii', $qtdItem, $idPeca);
        $stmtEstoque->execute();
    }

    $stmtPeca->close();
    $stmtItem->close();
    $stmtEstoque->close();

    $conn->commit();
    $transacaoAberta = false;

    unset($_SESSION['carrinho'], $_SESSION['entrega_temporaria']);

    json_response([
        'status' => $statusMp,
        'id' => $idTransacaoMp,
        'pedido_id' => $pedidoId,
        'mode' => resolver_modo_pagamento(),
        'status_detail' => $statusDetail,
    ], 200);
} catch (Throwable $e) {
    if ($transacaoAberta) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
        }
    }
    json_error($e->getMessage(), 500);
}
