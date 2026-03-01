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

function traduzirStatus($status)
{
    $mapa = [
        'approved'   => 'APROVADO',
        'rejected'   => 'REPROVADO',
        'pending'    => 'PENDENTE',
        'in_process' => 'EM PROCESSAMENTO',
        'cancelled'  => 'CANCELADO'
    ];

    return $mapa[$status] ?? strtoupper($status);
}


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
        /* ================================
   BASE GLOBAL
================================ */

        html,
        body {
            background-color: #000 !important;
            margin: 0;
            padding: 0;
            color: #f1f1f1 !important;
            font-family: sans-serif;
        }

        .text-secondary,
        small {
            color: #bbbbbb !important;
        }

        /* ================================
   NAVBAR
================================ */

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

        /* ================================
   ADMIN SECTION
================================ */

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

        .card-boxter h2,
        .card-boxter h5 {
            color: #ffffff !important;
        }

        .card-boxter div {
            color: #dddddd;
        }

        .cabecalho-vermelho {
            background-color: #cd221f !important;
            color: #fff !important;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px;
        }

        /* ================================
   TABELA
================================ */

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

        .tabela-boxter tbody tr:hover {
            background-color: #151515;
            transition: 0.2s;
        }

        /* ================================
   TOTAL
================================ */

        .total-label {
            color: #ffffff !important;
            font-weight: 600;
        }

        .total-value {
            font-weight: bold;
            font-size: 1.3rem;
            color: #00ff00;
        }

        .total-box {
            background-color: #000;
            border-top: 2px solid #cd221f;
            padding: 18px 25px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
        }

        /* ================================
   BOTÕES
================================ */

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

        /* ================================
   PEDIDO HEADER
================================ */

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

        /* ================================
   RESPONSIVIDADE
================================ */

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

            .tabela-boxter th,
            .tabela-boxter td {
                font-size: 0.85rem;
                padding: 10px !important;
                white-space: nowrap;
            }

        }

        /* MOBILE TABLE LAYOUT */

        @media (max-width: 768px) {

            .table-responsive {
                overflow-x: auto;
            }

            .tabela-boxter {
                min-width: 600px;
            }

            .total-box {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .btn-imprimir {
                width: 90%;
                max-width: 320px;
            }

            .tabela-boxter td.ps-4,
            .tabela-boxter th.ps-4 {
                padding-left: 12px !important;
            }

        }

        .btn-imprimir {
            background-color: #dc3545;
            border: none;
            padding: 10px 26px;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-imprimir:hover {
            background-color: #bb2d3b;
            transform: translateY(-1px);
        }

        /* ================================
   PRINT
================================ */

        @media print {

            header,
            .navbar,
            .nav-container,
            .boxter-admin-header,
            .btn,
            button,
            a,
            .no-print {
                display: none !important;
            }

            @page {
                margin: 0.5cm;
            }

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

            .table-responsive {
                overflow: visible !important;
            }

            .table-responsive::-webkit-scrollbar {
                display: none !important;
            }

            .tabela-boxter {
                width: 100% !important;
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
        <div class="container pt-4">

            <!-- 🔴 BLOCO 1 - IDENTIFICAÇÃO DO PEDIDO -->
            <div class="card card-boxter mb-4">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">

                    <div>
                        <h2 class="fw-bold mb-1">
                            PEDIDO #<?php echo $pedido['id_pedido']; ?>
                        </h2>
                        <small class="text-secondary">
                            Data: <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?>
                        </small>
                    </div>

                    <div class="text-end">
                        <?php
                        $status = strtolower($pedido['status_pedido']);

                        switch ($status) {
                            case 'approved':
                                $corStatus = '#28a745'; // verde
                                break;

                            case 'rejected':
                            case 'cancelled':
                                $corStatus = '#dc3545'; // vermelho
                                break;

                            case 'pending':
                            case 'in_process':
                                $corStatus = '#ffc107'; // amarelo
                                break;

                            default:
                                $corStatus = '#ffffff'; // branco se algo inesperado
                                break;
                        }
                        ?>

                        <div style="font-weight:bold; font-size:1.1rem; color: <?php echo $corStatus; ?>">
                            STATUS: <?php echo traduzirStatus($pedido['status_pedido']); ?>
                        </div>

                        <div style="font-size:1.3rem; font-weight:bold; color:#00ff00;">
                            TOTAL: R$ <?php echo number_format($pedido['valor_total_pedido'], 2, ',', '.'); ?>
                        </div>
                    </div>

                </div>
            </div>


            <div class="row">

                <!-- 🔵 BLOCO 2 - CLIENTE -->
                <div class="col-md-4 mb-4">
                    <div class="card card-boxter h-100">
                        <div class="cabecalho-vermelho text-center">CLIENTE</div>
                        <div class="card-body">

                            <!-- Nome -->
                            <h5 class="fw-bold mb-2">
                                <?php echo htmlspecialchars($pedido['nome_cliente']); ?>
                            </h5>

                            <!-- Telefone / WhatsApp -->
                            <div class="mb-3">

                                <?php if (!empty($pedido['telefone_cliente'])): ?>

                                    <a href="https://wa.me/55<?php echo preg_replace('/\D/', '', $pedido['telefone_cliente']); ?>"
                                        target="_blank"
                                        class="btn btn-success w-100 fw-semibold">
                                        <i class="bi bi-whatsapp"></i>
                                        <?php echo htmlspecialchars($pedido['telefone_cliente']); ?>
                                    </a>

                                <?php else: ?>

                                    <div class="alert text-center p-2 mb-0"
                                        style="background-color:#2a2a2a; color:#ffc107; border:1px solid #ffc107;">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Telefone não informado
                                    </div>

                                <?php endif; ?>

                            </div>

                            <hr>

                            <!-- Endereço -->
                            <div style="line-height:1.6; font-size:0.95rem;">
                                <strong>Endereço:</strong><br>

                                <?php if (!empty($pedido['entrega_logradouro'])): ?>
                                    <?php echo htmlspecialchars($pedido['entrega_logradouro']); ?>,
                                    <?php echo htmlspecialchars($pedido['entrega_numero']); ?><br>
                                <?php else: ?>
                                    <span class="text-warning">Endereço não informado</span><br>
                                <?php endif; ?>

                                <?php if (!empty($pedido['entrega_complemento'])): ?>
                                    <?php echo htmlspecialchars($pedido['entrega_complemento']); ?><br>
                                <?php endif; ?>

                                <?php if (!empty($pedido['entrega_bairro'])): ?>
                                    <?php echo htmlspecialchars($pedido['entrega_bairro']); ?><br>
                                <?php endif; ?>

                                <?php if (!empty($pedido['entrega_cidade'])): ?>
                                    <?php echo htmlspecialchars($pedido['entrega_cidade']); ?> -
                                    <?php echo htmlspecialchars($pedido['entrega_uf']); ?><br>
                                <?php endif; ?>

                                <?php if (!empty($pedido['entrega_cep'])): ?>
                                    CEP: <?php echo htmlspecialchars($pedido['entrega_cep']); ?>
                                <?php endif; ?>

                            </div>

                        </div>
                    </div>
                </div>

                <!-- 🟡 BLOCO 3 - PRODUTOS -->
                <div class="col-md-8 mb-4">
                    <div class="card card-boxter">
                        <div class="cabecalho-vermelho">PRODUTOS DO PEDIDO</div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table tabela-boxter mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-start ps-4">Produto</th>
                                            <th>Qtd</th>
                                            <th>Valor Unit.</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $totalCalculado = 0;
                                        while ($item = mysqli_fetch_assoc($res_itens)):

                                            $subtotal = $item['qtde_item'] * $item['preco_unit_item'];
                                            $totalCalculado += $subtotal;
                                        ?>

                                            <tr>
                                                <td class="text-start ps-4 fw-semibold" data-label="Produto">
                                                    <?php echo $item['nome_peca']; ?>
                                                </td>

                                                <td data-label="Qtd">
                                                    <?php echo $item['qtde_item']; ?>
                                                </td>

                                                <td data-label="Valor Unit.">
                                                    R$ <?php echo number_format($item['preco_unit_item'], 2, ',', '.'); ?>
                                                </td>

                                                <td style="color:#00ff00;" data-label="Subtotal">
                                                    R$ <?php echo number_format($subtotal, 2, ',', '.'); ?>
                                                </td>
                                            </tr>

                                        <?php endwhile; ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ⚫ TOTAL GRANDE -->
                    <div style="background:#000; border-top:2px solid #cd221f; padding:20px; text-align:right;">

                        <div style="font-size:1rem; margin-bottom:5px;">
                            <span style="color:#ccc;">Subtotal Produtos:</span>
                            <span style="color:#fff;">
                                R$ <?php echo number_format($totalCalculado, 2, ',', '.'); ?>
                            </span>
                        </div>

                        <div style="font-size:1rem; margin-bottom:10px;">
                            <span style="color:#ccc;">Frete:</span>
                            <span style="color:#fff;">
                                R$ <?php echo number_format($pedido['valor_frete'], 2, ',', '.'); ?>
                            </span>
                        </div>

                        <div style="font-size:1.5rem; font-weight:bold; color:#00ff00;">
                            TOTAL GERAL:
                            R$ <?php echo number_format($pedido['valor_total_pedido'], 2, ',', '.'); ?>
                        </div>

                    </div>

                </div>
            </div>
            <!-- 🔘 AÇÕES -->
            <div class="row mt-4">
                <div class="col-12 text-end">
                    <button type="button"
                        class="btn btn-imprimir"
                        onclick="window.print();">
                        <i class="bi bi-printer me-2"></i>
                        IMPRIMIR ETIQUETA
                    </button>
                </div>
            </div>
        </div>



    </div>
</body>

</html>