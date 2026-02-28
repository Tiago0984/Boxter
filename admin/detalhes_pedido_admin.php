<?php
session_start();
include_once "../conexao.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id_pedido = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

// Consulta do Pedido + Cliente (usando telefone_cliente que confirmamos)
$sql_pedido = "SELECT p.*, c.nome_cliente, c.telefone_cliente 
               FROM tbl_pedido p 
               INNER JOIN tbl_clientes c ON p.id_cliente = c.id_cliente 
               WHERE p.id_pedido = '$id_pedido'";
$res_pedido = mysqli_query($conn, $sql_pedido);
$pedido = mysqli_fetch_assoc($res_pedido);

if (!$pedido) {
    die("Pedido não encontrado.");
}

// Consulta dos Itens
$sql_itens = "SELECT i.*, p.nome_peca 
              FROM tbl_itens_pedido i
              INNER JOIN tbl_pecas p ON i.id_peca = p.id_peca
              WHERE i.id_pedido = '$id_pedido'";
$res_itens = mysqli_query($conn, $sql_itens);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes Pedido #<?php echo $id_pedido; ?> - Boxter Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin-responsive.css">

    <style>
        /* 1. Fundo Preto Total (Padrão Estoque) */
        html,
        body {
            background-color: #000 !important;
            margin: 0;
            padding: 0;
            color: white;
            font-family: sans-serif;
        }

        /* 2. Menu Superior (Padrão Navbar que você mandou) */
        .navbar {
            background-color: #000 !important;
            margin-bottom: 0 !important;
            border-bottom: 2px solid #cd221f !important;
            padding: 15px 0;
        }

        .navbar-nav .nav-link {
            color: #ffffff !important;
            text-decoration: none !important;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #0056b3 !important;
        }

        /* 3. Estilo da Seção e Tabela (Padrão Admin) */
        .admin-section {
            background-color: #000;
            min-height: 100vh;
            padding-top: 40px;
        }

        .card-boxter {
            background-color: #111;
            border: 1px solid #333;
            border-radius: 0;
        }

        .cabecalho-vermelho {
            background-color: #cd221f !important;
            color: #fff !important;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px;
        }

        .tabela-boxter {
            background-color: #111;
            border: 1px solid #333;
            color: #fff;
            margin-bottom: 0;
        }

        .tabela-boxter th {
            background-color: #222;
            color: #ccc;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-bottom: 1px solid #333;
            text-align: center;
        }

        .tabela-boxter td {
            border-color: #333;
            vertical-align: middle;
            text-align: center;
            padding: 12px;
        }

        .text-valor-total {
            color: #28a745;
            font-size: 1.4rem;
            font-weight: bold;
        }

        .btn-boxter-border {
            border: 1px solid #444;
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            transition: 0.3s;
        }

        .btn-boxter-border:hover {
            background-color: #fff;
            color: #000;
        }

        .detalhes-texto {
            color: #e4e4e4 !important;
        }

        .detalhes-label {
            color: #cd221f;
            /* Mantém o rótulo em vermelho para destaque */
            font-weight: bold;
            margin-right: 5px;
        }

        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .pedido-header h2 {
            font-size: 1.6rem;
            line-height: 1.2;
        }

        .itens-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 991px) {
            .admin-section {
                padding-top: 20px;
            }

            .pedido-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }

            .pedido-header h2 {
                font-size: 1.15rem !important;
            }

            .card-body {
                padding: 14px !important;
            }

            .cabecalho-vermelho {
                font-size: 0.85rem;
                letter-spacing: 0.5px;
            }

            .itens-wrapper table {
                min-width: 620px;
            }

            .tabela-boxter th,
            .tabela-boxter td {
                font-size: 0.85rem;
                padding: 10px !important;
                white-space: nowrap;
            }

            .text-valor-total {
                font-size: 1.05rem;
            }

            .btn-imprimir {
                width: 100%;
                padding-left: 0 !important;
                padding-right: 0 !important;
                border-radius: 8px !important;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 767.98px) {
            .itens-wrapper {
                overflow: visible;
            }

            .itens-wrapper table {
                min-width: 100%;
            }

            .tabela-boxter {
                border: 0;
                background: transparent;
            }

            .tabela-boxter thead {
                display: none;
            }

            .tabela-boxter tbody,
            .tabela-boxter tbody tr,
            .tabela-boxter tbody td,
            .tabela-boxter tfoot,
            .tabela-boxter tfoot tr,
            .tabela-boxter tfoot td {
                display: block;
                width: 100%;
            }

            .tabela-boxter tbody tr {
                background-color: #111;
                border: 1px solid #333;
                border-radius: 10px;
                margin: 10px 10px 0;
                overflow: hidden;
            }

            .tabela-boxter tbody td {
                text-align: left !important;
                padding: 10px 14px !important;
                border: 0;
                border-bottom: 1px solid #2a2a2a;
                white-space: normal;
            }

            .tabela-boxter tbody td:last-child {
                border-bottom: 0;
            }

            .tabela-boxter tbody td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 2px;
                font-size: 0.72rem;
                font-weight: 700;
                color: #9aa0a6;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .tabela-boxter tfoot tr {
                background-color: #080808;
                border: 1px solid #333;
                border-radius: 10px;
                margin: 10px;
                padding: 12px 14px;
            }

            .tabela-boxter tfoot td {
                border: 0;
                padding: 0 !important;
                text-align: left !important;
                white-space: normal;
            }

            .tabela-boxter .total-label {
                color: #e4e4e4;
                margin-bottom: 4px;
            }
        }

        @media (max-width: 576px) {
            .container.pt-5 {
                padding-top: 0.75rem !important;
            }

            .pedido-header h2 {
                font-size: 1rem !important;
            }
        }

        @media print {

            /* Esconde o Header (Navegação) */
            header,
            .navbar,
            .nav-container,
            .boxter-admin-header {
                display: none !important;
            }

            /* Esconde os botões Voltar e Imprimir Etiqueta */
            .btn,
            button,
            a.btn-outline-secondary,
            .no-print {
                display: none !important;
            }

            /* Esconde o rodapé da página (URL e data que o navegador coloca) */
            @page {
                margin: 0.5cm;
            }

            /* Remove sombras e bordas coloridas para economizar tinta e focar no conteúdo */
            body {
                background-color: #fff !important;
                color: #000 !important;
            }

            .card,
            .container {
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php" style="color: #cd221f;">BOXTER ADMIN</a>
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <a href="index.php" class="nav-link px-3">Início</a>
                <a href="estoque.php" class="nav-link px-3">Estoque</a>
                <a href="pedidos.php" class="nav-link px-3 active">Vendas</a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger ms-3">Sair</a>
            </div>
        </div>
    </nav>

    <div class="admin-section">
        <div class="container pt-5">
            <div class="pedido-header mb-4">
                <h2 class="fw-bold m-0"><i class="bi bi-receipt"></i> DETALHES DO PEDIDO #<?php echo $id_pedido; ?></h2>
                <a href="pedidos.php" class="btn btn-outline-secondary d-print-none">VOLTAR</a>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card card-boxter h-100" style="background-color: #111; border: 1px solid #333;">
                        <div class="cabecalho-vermelho" style="background-color: #cd221f; color: #fff; padding: 10px; text-align: center; font-weight: bold;">DADOS DE ENTREGA</div>
                        <div class="card-body" style="color: #e4e4e4;">
                            <h5 class="fw-bold" style="color: #fff;"><?php echo $pedido['nome_cliente']; ?></h5>
                            <p class="small mb-3" style="color: #e4e4e4;">
                                <a href="https://wa.me/55<?php echo preg_replace('/\D/', '', $pedido['telefone_cliente']); ?>" target="_blank" class="text-decoration-none" style="color: inherit;">
                                    <i class="bi bi-whatsapp text-success"></i> <?php echo $pedido['telefone_cliente'] ?: 'N/A'; ?>
                                </a>
                            </p>
                            <hr style="border-color: #444;">

                            <p class="small mb-0" style="line-height: 1.6;">
                                <strong style="color: #cd221f;">RUA:</strong> <?php echo $pedido['entrega_logradouro'] ?: 'NÃO INFORMADO'; ?>, <?php echo $pedido['entrega_numero']; ?><br>

                                <?php if (!empty($pedido['entrega_complemento'])): ?>
                                    <strong style="color: #cd221f;">COMPLEMENTO:</strong> <?php echo $pedido['entrega_complemento']; ?><br>
                                <?php endif; ?>

                                <strong style="color: #cd221f;">BAIRRO:</strong> <?php echo $pedido['entrega_bairro']; ?><br>
                                <strong style="color: #cd221f;">CIDADE:</strong> <?php echo $pedido['entrega_cidade']; ?> / <?php echo $pedido['entrega_uf']; ?><br>
                                <strong style="color: #cd221f;">CEP:</strong> <?php echo $pedido['entrega_cep']; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8 mb-4">
                    <div class="card card-boxter h-100">
                        <div class="cabecalho-vermelho">ITENS DO PEDIDO</div>
                        <div class="card-body p-0 itens-wrapper">
                            <table class="table tabela-boxter">
                                <thead>
                                    <tr style="border-bottom: 1px solid #444 !important;">
                                        <th class="py-3 text-start ps-4" style="width: 50%; color: #e4e4e4; border: none !important;">PRODUTO</th>
                                        <th class="py-3" style="color: #e4e4e4; border: none !important;">Qtd</th>
                                        <th class="py-3 text-end pe-4" style="color: #e4e4e4; border: none !important;">Preço Unit.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = mysqli_fetch_assoc($res_itens)): ?>
                                        <tr>
                                            <td data-label="Produto" class="text-start ps-4 fw-bold text-danger"><?php echo $item['nome_peca']; ?></td>
                                            <td data-label="Qtd"><?php echo $item['qtde_item']; ?></td>
                                            <td data-label="Preco Unitario" class="text-end pe-4">R$ <?php echo number_format($item['preco_unit_item'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #080808;">
                                        <td colspan="2" class="text-end fw-bold py-3 total-label">VALOR TOTAL:</td>
                                        <td class="text-end pe-4 py-3 text-valor-total total-value">
                                            R$ <?php echo number_format($pedido['valor_total_pedido'], 2, ',', '.'); ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-2">
                <button onclick="window.print()" class="btn btn-danger btn-lg rounded-0 px-5 fw-bold btn-imprimir">
                    <i class="bi bi-printer"></i> IMPRIMIR ETIQUETA
                </button>
            </div>
        </div>
    </div>

</body>

</html>
