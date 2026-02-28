<?php
set_time_limit(60);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/log_webhook.txt');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
include_once "conexao.php";

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

http_response_code(200); // MP exige 200

$input = file_get_contents("php://input");
$body = json_decode($input, true);

if (!isset($body['data']['id'])) {
    exit; // nada para processar
}

$paymentId = $body['data']['id'];

try {

    // 🔐 Configura token
    $accessToken = trim((string)(getenv('MP_ACCESS_TOKEN') ?: ''));
    if ($accessToken === '') {
        throw new RuntimeException("MP_ACCESS_TOKEN nao configurado.");
    }

    MercadoPagoConfig::setAccessToken($accessToken);
    MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);

    $client = new PaymentClient();
    $payment = $client->get($paymentId);

    if (!$payment) {
        throw new RuntimeException("Pagamento nao encontrado.");
    }

    $pedidoId = (int)$payment->external_reference;
    $statusMp = strtolower((string)$payment->status);

    if ($pedidoId <= 0) {
        throw new RuntimeException("External reference invalida.");
    }

    // 🔎 Busca pedido
    $stmt = $conn->prepare("SELECT status_pedido FROM tbl_pedido WHERE id_pedido = ?");
    $stmt->bind_param('i', $pedidoId);
    $stmt->execute();
    $res = $stmt->get_result();
    $pedido = $res->fetch_assoc();
    $stmt->close();

    if (!$pedido) {
        throw new RuntimeException("Pedido nao encontrado.");
    }

    // 🔁 Idempotência — se já aprovado, não faz nada
    if ($pedido['status_pedido'] === 'approved') {
        exit;
    }

    // 🔄 Atualiza status
    $stmtUpdate = $conn->prepare(
        "UPDATE tbl_pedido 
         SET status_pedido = ?, id_transacao_mp = ?
         WHERE id_pedido = ?"
    );
    $stmtUpdate->bind_param(
        'ssi',
        $statusMp,
        $paymentId,
        $pedidoId
    );
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // 📦 Se aprovado, baixa estoque
    if ($statusMp === 'approved') {

        $conn->begin_transaction();

        $stmtItens = $conn->prepare(
            "SELECT id_peca, qtde_item 
             FROM tbl_itens_pedido 
             WHERE id_pedido = ?"
        );
        $stmtItens->bind_param('i', $pedidoId);
        $stmtItens->execute();
        $resItens = $stmtItens->get_result();

        $stmtPeca = $conn->prepare(
            "SELECT estoque_peca FROM tbl_pecas WHERE id_peca = ? FOR UPDATE"
        );
        $stmtEstoque = $conn->prepare(
            "UPDATE tbl_pecas SET estoque_peca = estoque_peca - ? WHERE id_peca = ?"
        );

        while ($item = $resItens->fetch_assoc()) {

            $idPeca = (int)$item['id_peca'];
            $qtd = (int)$item['qtde_item'];

            $stmtPeca->bind_param('i', $idPeca);
            $stmtPeca->execute();
            $resPeca = $stmtPeca->get_result();
            $peca = $resPeca->fetch_assoc();

            if (!$peca || $peca['estoque_peca'] < $qtd) {
                throw new RuntimeException("Estoque insuficiente no webhook.");
            }

            $stmtEstoque->bind_param('ii', $qtd, $idPeca);
            $stmtEstoque->execute();
        }

        $stmtItens->close();
        $stmtPeca->close();
        $stmtEstoque->close();

        $conn->commit();
    }
} catch (Throwable $e) {

    try {
        $conn->rollback();
    } catch (Throwable $ignore) {
    }

    error_log("Erro webhook: " . $e->getMessage());
    exit;
}
