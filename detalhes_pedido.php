<?php
session_start();
include_once "conexao.php";

if (!isset($_SESSION['cliente_id']) || !isset($_GET['id'])) {
    header("Location: meus_pedidos.php");
    exit;
}

$id_pedido = (int)$_GET['id'];
$id_cliente = (int)$_SESSION['cliente_id'];

// Garante que o pedido pertence ao cliente logado.
$sql_pedido = "SELECT * FROM tbl_pedido WHERE id_pedido = ? AND id_cliente = ?";
$stmt_pedido = mysqli_prepare($conn, $sql_pedido);
mysqli_stmt_bind_param($stmt_pedido, "ii", $id_pedido, $id_cliente);
mysqli_stmt_execute($stmt_pedido);
$res_pedido = mysqli_stmt_get_result($stmt_pedido);
$pedido = mysqli_fetch_assoc($res_pedido);

if (!$pedido) {
    die("Pedido nao encontrado ou acesso negado.");
}

// Corrigido: usa img_peca (coluna existente em tbl_pecas).
$sql_itens = "SELECT i.*, p.nome_peca, p.img_peca 
              FROM tbl_itens_pedido i
              INNER JOIN tbl_pecas p ON i.id_peca = p.id_peca
              WHERE i.id_pedido = ?";
$stmt_itens = mysqli_prepare($conn, $sql_itens);
mysqli_stmt_bind_param($stmt_itens, "i", $id_pedido);
mysqli_stmt_execute($stmt_itens);
$res_itens = mysqli_stmt_get_result($stmt_itens);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?php echo $id_pedido; ?> - Boxter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>

<body style="background-color: #0b0b0b; color: white;">

    <?php include_once("conteudo/topo.php"); ?>

    <main class="container my-5 detalhes-pedido-page" style="padding-top: 130px;">
        <div class="card border-secondary detalhes-card" style="background-color: #1a1a1a;">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center py-3 detalhes-pedido-header">
                <h4 class="m-0 text-danger fw-bold text-uppercase">Detalhes do Pedido #<?php echo $id_pedido; ?></h4>
                <?php
                $statusDetalhe = strtoupper($pedido['status_pedido']);
                $statusDetalheLabel = ($statusDetalhe === 'APPROVED' || $statusDetalhe === 'APROVADO') ? 'Aprovado' : 'Pendente';
                $statusDetalheClass = ($statusDetalheLabel === 'Aprovado') ? 'bg-success' : 'bg-warning text-dark';
                ?>
                <span class="badge <?php echo $statusDetalheClass; ?>"><?php echo $statusDetalheLabel; ?></span>
            </div>

            <div class="card-body">
                <p class="mb-4 detalhes-data-realizacao">Realizado em: <strong><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></strong></p>

                <div class="table-responsive detalhes-pedido-wrapper">
                    <table class="table table-dark align-middle detalhes-table">
                        <colgroup>
                            <col style="width: 52%;">
                            <col style="width: 12%;">
                            <col style="width: 18%;">
                            <col style="width: 18%;">
                        </colgroup>
                        <thead>
                            <tr class="detalhes-head-row">
                                <th>Item</th>
                                <th>Qtd</th>
                                <th>Preco Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = mysqli_fetch_assoc($res_itens)): ?>
                                <tr>
                                    <td data-label="Item">
                                        <div class="d-flex align-items-center detalhes-item-cell">
                                            <img src="img/<?php echo $item['img_peca']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover;" class="rounded me-3">
                                            <span><?php echo $item['nome_peca']; ?></span>
                                        </div>
                                    </td>
                                    <td data-label="Qtd"><?php echo $item['qtde_item']; ?></td>
                                    <td data-label="Preco Unit.">R$ <?php echo number_format($item['preco_unit_item'], 2, ',', '.'); ?></td>
                                    <td class="text-end fw-bold" data-label="Subtotal">
                                        R$ <?php echo number_format($item['qtde_item'] * $item['preco_unit_item'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end detalhes-total-label">Valor Total do Pedido:</td>
                                <td class="text-end fs-4 fw-bold text-success detalhes-total-value">
                                    R$ <?php echo number_format($pedido['valor_total_pedido'], 2, ',', '.'); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="meus_pedidos.php" class="btn btn-outline-light detalhes-btn-voltar">VOLTAR AOS PEDIDOS</a>
                </div>
            </div>
        </div>
    </main>

    <?php include_once("conteudo/rodape.php"); ?>
</body>

</html>