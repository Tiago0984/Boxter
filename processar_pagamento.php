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
    json_response(['status' => 'error', 'message' => $message], $statusCode);
}

function resolver_access_token(): string
{
    $modo = strtolower((string)(getenv('APP_PAYMENT_MODE') ?: 'sandbox'));
    $token = trim((string)(getenv('MP_ACCESS_TOKEN') ?: ''));
    if ($token === '' && $modo !== 'production') {
        $token = trim((string)(getenv('MP_ACCESS_TOKEN_TEST') ?: ''));
    }
    if ($token === '') {
        throw new RuntimeException('MP_ACCESS_TOKEN nao configurado.');
    }
    if ($modo === 'production' && stripos($token, 'TEST-') === 0) {
        throw new RuntimeException('MP_ACCESS_TOKEN de teste usado em producao.');
    }
    return $token;
}

function validar_payload_pagamento(array $data): void
{
    if (empty($data['token'])) {
        json_error('Token de pagamento ausente.', 422);
    }
    if (empty($data['installments']) || (int)$data['installments'] <= 0) {
        json_error('Numero de parcelas invalido.', 422);
    }
    if (empty($data['payment_method_id'])) {
        json_error('Metodo de pagamento ausente.', 422);
    }
    if (empty($data['payer']['email'])) {
        json_error('Email do pagador ausente.', 422);
    }
    if (empty($data['payer']['identification']['type']) || empty($data['payer']['identification']['number'])) {
        json_error('Documento do pagador ausente.', 422);
    }
}

function calcular_totais(mysqli $conn, array $carrinho): float
{
    $total = 0.0;
    $stmt = $conn->prepare("SELECT preco_venda_peca FROM tbl_pecas WHERE id_peca = ?");
    foreach ($carrinho as $id => $qtd) {
        $id = (int)$id;
        $qtd = (int)$qtd;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $peca = $res->fetch_assoc();
        if (!$peca) {
            throw new RuntimeException("Peca nao encontrada.");
        }
        $total += (float)$peca['preco_venda_peca'] * $qtd;
    }
    $stmt->close();
    return $total;
}

function criar_pagamento_mp(array $data, float $valor, int $pedidoId): array
{
    MercadoPagoConfig::setAccessToken(resolver_access_token());
    MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);

    $client = new PaymentClient();

    $request = [
        'transaction_amount' => round($valor, 2),
        'token' => $data['token'],
        'description' => 'Compra Boxter Auto Pecas',
        'installments' => (int)$data['installments'],
        'payment_method_id' => $data['payment_method_id'],
        'payer' => [
            'email' => $data['payer']['email'],
            'identification' => [
                'type' => $data['payer']['identification']['type'],
                'number' => $data['payer']['identification']['number']
            ]
        ],
        'external_reference' => (string)$pedidoId,
        'binary_mode' => false
    ];

    $requestOptions = new RequestOptions();
    $requestOptions->setCustomHeaders([
        "X-Idempotency-Key: " . hash('sha256', $pedidoId . '|' . $valor)
    ]);

    try {

        $payment = $client->create($request, $requestOptions);

        return [
            'id' => (string)$payment->id,
            'status' => strtolower((string)$payment->status),
            'status_detail' => (string)$payment->status_detail
        ];
    } catch (MPApiException $e) {

        $apiResponse = $e->getApiResponse();

        throw new RuntimeException(json_encode([
            'mp_status_code' => $apiResponse ? $apiResponse->getStatusCode() : null,
            'mp_response' => $apiResponse ? $apiResponse->getContent() : null
        ]));
    }
}

if (empty($_SESSION['cliente_id'])) {
    json_error('Sessao expirada.', 401);
}

if (empty($_SESSION['carrinho'])) {
    json_error('Carrinho vazio.', 422);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    json_error('Payload invalido.');
}
validar_payload_pagamento($data);

$idCliente = (int)$_SESSION['cliente_id'];

try {

    // 1️⃣ Calcula total
    $valorProdutos = $_SESSION['subtotal_produtos'] ?? calcular_totais($conn, $_SESSION['carrinho']);
    $valorFrete = $_SESSION['valor_frete'] ?? 0.00;

    $valorTotal = $valorProdutos + $valorFrete;

    // 2️⃣ Cria pedido ANTES do pagamento
    $conn->begin_transaction();

    $statusInicial = 'aguardando_pagamento';

    // 🔎 Pega dados de entrega da sessão
    $entrega = $_SESSION['entrega_temporaria'] ?? [];

    $cep = $entrega['cep'] ?? null;
    $logradouro = $entrega['logradouro'] ?? null;
    $numero = $entrega['numero'] ?? null;
    $complemento = $entrega['complemento'] ?? null;
    $bairro = $entrega['bairro'] ?? null;
    $cidade = $entrega['cidade'] ?? null;
    $uf = $entrega['uf'] ?? null;

    if (!empty($entrega['usar_cadastro'])) {

        // Busca endereço do cliente no banco
        $stmtCliente = $conn->prepare("
    SELECT 
        cep_cliente,
        endereco_cliente,
        numero_cliente,
        complemento_cliente,
        bairro_cliente,
        cidade_cliente,
        uf_cliente
    FROM tbl_clientes
    WHERE id_cliente = ?
");

        $stmtCliente->bind_param('i', $idCliente);
        $stmtCliente->execute();
        $resCliente = $stmtCliente->get_result();
        $dadosCliente = $resCliente->fetch_assoc();
        $stmtCliente->close();

        if (!$dadosCliente) {
            throw new RuntimeException("Endereço do cliente não encontrado.");
        }

        $cep = $dadosCliente['cep_cliente'] ?? null;
        $logradouro = $dadosCliente['endereco_cliente'] ?? null;
        $numero = $dadosCliente['numero_cliente'] ?? null;
        $complemento = $dadosCliente['complemento_cliente'] ?? null;
        $bairro = $dadosCliente['bairro_cliente'] ?? null;
        $cidade = $dadosCliente['cidade_cliente'] ?? null;
        $uf = $dadosCliente['uf_cliente'] ?? null;
    } else {

        // Usa endereço da sessão
        $cep = $entrega['cep'] ?? null;
        $logradouro = $entrega['logradouro'] ?? null;
        $numero = $entrega['numero'] ?? null;
        $complemento = $entrega['complemento'] ?? null;
        $bairro = $entrega['bairro'] ?? null;
        $cidade = $entrega['cidade'] ?? null;
        $uf = $entrega['uf'] ?? null;
    }

    $stmtPedido = $conn->prepare(
        "INSERT INTO tbl_pedido (
        id_cliente,
        data_pedido,
        status_pedido,
        valor_total_pedido,
        valor_frete,
        entrega_cep,
        entrega_logradouro,
        entrega_numero,
        entrega_complemento,
        entrega_bairro,
        entrega_cidade,
        entrega_uf
    ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmtPedido->bind_param(
        'isddsssssss',
        $idCliente,
        $statusInicial,
        $valorTotal,
        $valorFrete,
        $cep,
        $logradouro,
        $numero,
        $complemento,
        $bairro,
        $cidade,
        $uf
    );

    $stmtPedido->execute();
    $pedidoId = (int)$conn->insert_id;
    $stmtPedido->close();

    // 2️⃣.1 - Salva os itens do pedido
    $stmtItem = $conn->prepare("
    INSERT INTO tbl_itens_pedido (
        id_pedido,
        id_peca,
        qtde_item,
        preco_unit_item
    ) VALUES (?, ?, ?, ?)
");

    $stmtPreco = $conn->prepare("
    SELECT preco_venda_peca 
    FROM tbl_pecas 
    WHERE id_peca = ?
");

    foreach ($_SESSION['carrinho'] as $idPeca => $qtd) {

        // Busca preço atual da peça
        $stmtPreco->bind_param('i', $idPeca);
        $stmtPreco->execute();
        $resPreco = $stmtPreco->get_result();
        $peca = $resPreco->fetch_assoc();

        if (!$peca) {
            throw new RuntimeException("Peça não encontrada ao salvar item.");
        }

        $precoUnit = (float)$peca['preco_venda_peca'];

        // Salva item
        $stmtItem->bind_param(
            'iiid',
            $pedidoId,
            $idPeca,
            $qtd,
            $precoUnit
        );

        $stmtItem->execute();
    }

    $stmtItem->close();
    $stmtPreco->close();

    $conn->commit();

    // 3️⃣ Processa pagamento
    $pagamento = criar_pagamento_mp($data, $valorTotal, $pedidoId);

    $statusMp = $pagamento['status'];
    $idTransacaoMp = $pagamento['id'];

    // 4️⃣ Atualiza pedido com status real
    $stmtUpdate = $conn->prepare(
        "UPDATE tbl_pedido 
         SET status_pedido = ?, id_transacao_mp = ?
         WHERE id_pedido = ?"
    );

    $stmtUpdate->bind_param('ssi', $statusMp, $idTransacaoMp, $pedidoId);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // 5️⃣ Se aprovado, baixa estoque
    if ($statusMp === 'approved') {

        $conn->begin_transaction();

        $stmtPeca = $conn->prepare("SELECT estoque_peca FROM tbl_pecas WHERE id_peca = ? FOR UPDATE");
        $stmtEstoque = $conn->prepare("UPDATE tbl_pecas SET estoque_peca = estoque_peca - ? WHERE id_peca = ?");

        foreach ($_SESSION['carrinho'] as $idPeca => $qtd) {

            $idPeca = (int)$idPeca;
            $qtd = (int)$qtd;

            $stmtPeca->bind_param('i', $idPeca);
            $stmtPeca->execute();
            $res = $stmtPeca->get_result();
            $peca = $res->fetch_assoc();

            if (!$peca || $peca['estoque_peca'] < $qtd) {
                throw new RuntimeException("Estoque insuficiente.");
            }

            $stmtEstoque->bind_param('ii', $qtd, $idPeca);
            $stmtEstoque->execute();
        }

        $stmtPeca->close();
        $stmtEstoque->close();

        $conn->commit();
    }

    unset($_SESSION['carrinho']);

    json_response([
        'status' => $statusMp,
        'pedido_id' => $pedidoId,
        'id' => $idTransacaoMp
    ]);
} catch (Throwable $e) {
    error_log("Erro pagamento: " . $e->getMessage());
    try {
        $conn->rollback();
    } catch (Throwable $ignore) {
    }
    json_error($e->getMessage(), 500);
}
