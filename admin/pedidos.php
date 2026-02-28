<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
// Caminho corrigido para a raiz
include_once "../conexao.php";

// LÓGICA DE ADMIN: Buscamos todos os pedidos e cruzamos com a tabela de clientes
$sql = "SELECT p.*, c.nome_cliente 
        FROM tbl_pedido p 
        INNER JOIN tbl_clientes c ON p.id_cliente = c.id_cliente 
        ORDER BY p.data_pedido DESC";
$res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Pedidos Boxter</title>

    <link rel="apple-touch-icon" sizes="180x180" href="../img/icon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/icon/favicon-32x32.png">
    <link rel="stylesheet" href="../css/reset.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="admin-responsive.css">
    <!-- <link rel="stylesheet" href="../css/estilo.css"> -->

    <style>
        /* 1. Garante fundo preto total */
        html,
        body {
            background-color: #000 !important;
            margin: 0;
            padding: 0;
            color: white;
        }

        /* 2. Ajuste da Navbar */
        .navbar {
            background-color: #000 !important;
            margin-bottom: 0 !important;
            border-bottom: 2px solid #cd221f !important;
            padding: 15px 0;
        }

        /* 3. Estrutura da Seção */
        .admin-section {
            background-color: #000;
            min-height: 90vh;
            padding-top: 100px;
        }

        /* 4. Tabela */
        .table-admin {
            background-color: #111;
            border: 1px solid #333;
            color: #fff;
        }

        .table-admin th {
            background-color: #cd221f;
            color: #fff;
            text-align: center;
        }

        /* 5. CORREÇÃO DOS LINKS (Removi o .navbar-nav e foquei na .navbar direto) */
        .navbar .nav-link {
            color: #ffffff !important;
            /* Cor padrão branca */
            text-decoration: none !important;
            transition: color 0.3s ease;
        }

        /* Hover e Ativo - Mudei para o AZUL que você usou no estoque */
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #0056b3 !important;
            background: none !important;
        }

        .pedidos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .pedidos-table td {
            vertical-align: middle;
        }

        @media (max-width: 767.98px) {
            .admin-section {
                padding-top: 32px;
            }

            .pedidos-title {
                font-size: clamp(1.35rem, 7vw, 1.75rem) !important;
            }

            .pedidos-table {
                min-width: 100% !important;
            }

            .pedidos-table thead {
                display: none;
            }

            .pedidos-table,
            .pedidos-table tbody,
            .pedidos-table tr,
            .pedidos-table td {
                display: block;
                width: 100%;
            }

            .pedidos-table tbody {
                background-color: transparent !important;
            }

            .pedidos-table tr {
                background-color: #fff;
                border: 1px solid #e5e5e5;
                border-radius: 12px;
                margin-bottom: 12px;
                overflow: hidden;
            }

            .pedidos-table td {
                border: 0 !important;
                border-bottom: 1px solid #f0f0f0 !important;
                padding: 10px 14px !important;
                text-align: left !important;
            }

            .pedidos-table td:last-child {
                border-bottom: 0 !important;
                padding-bottom: 14px !important;
            }

            .pedidos-table td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 3px;
                font-size: 0.72rem;
                font-weight: 600;
                letter-spacing: 0.03em;
                color: #6c757d;
                text-transform: uppercase;
            }

            .pedidos-table .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger" href="index.php">BOXTER ADMIN</a>

            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <a href="index.php" class="nav-link px-3">Início</a>
                <a href="estoque.php" class="nav-link px-3">Estoque</a>
                <a href="pedidos.php" class="nav-link px-3">Vendas</a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger ms-3">Sair</a>
            </div>
        </div>
    </nav>
    <main class="admin-section">
        <div class="container">
            <div class="pedidos-header">
                <h1 class="text-light pedidos-title" style="font-size: 32px; font-weight: 500;">
                    <i class="bi bi-cart-check" style="color: #cd221f;"></i> Gestão de Vendas
                </h1>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle pedidos-table" style="background-color: transparent !important; border-collapse: separate; border-spacing: 0;">
                    <thead style="border: none;">
                        <tr style="border: none;">
                            <th class="py-3 px-3" style="width: 10%; background-color: #cd221f !important; color: #ffffff !important; border: none; border-top-left-radius: 10px !important;">ID Pedido</th>

                            <th class="py-3" style="width: 20%; background-color: #cd221f !important; color: #ffffff !important; border: none;">Cliente</th>
                            <th class="py-3" style="width: 20%; background-color: #cd221f !important; color: #ffffff !important; border: none;">Data/Hora</th>
                            <th class="py-3" style="width: 15%; background-color: #cd221f !important; color: #ffffff !important; border: none;">Valor Total</th>
                            <th class="py-3 text-center" style="width: 15%; background-color: #cd221f !important; color: #ffffff !important; border: none;">Status Pagamento</th>

                            <th class="py-3 text-center" style="width: 20%; background-color: #cd221f !important; color: #ffffff !important; border: none; border-top-right-radius: 10px !important;">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark" style="background-color: #fff;">
                        <?php while ($pedido = mysqli_fetch_assoc($res)): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td data-label="ID Pedido" class="px-3 text-secondary">#<?php echo $pedido['id_pedido']; ?></td>
                                <td data-label="Cliente" class="fw-bold"><?php echo $pedido['nome_cliente']; ?></td>
                                <td data-label="Data/Hora"><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>

                                <td data-label="Valor Total" class="fw-bold">R$ <?php echo number_format($pedido['valor_total_pedido'], 2, ',', '.'); ?></td>

                                <td data-label="Status Pagamento" class="text-center">
                                    <?php
                                    $status = $pedido['status_pedido'];
                                    if ($status == 'approved') {
                                        echo '<span class="badge bg-success" style="padding: 8px 12px; border-radius: 20px;">
                        <i class="bi bi-check-lg me-1"></i>Aprovado
                      </span>';
                                    } else {
                                        echo '<span class="badge bg-warning text-dark" style="padding: 8px 12px; border-radius: 20px;">
                        <i class="bi bi-clock me-1"></i>Pendente
                      </span>';
                                    }
                                    ?>
                                </td>

                                <td data-label="Acoes" class="text-center">
                                    <a href="detalhes_pedido_admin.php?id=<?php echo $pedido['id_pedido']; ?>" class="btn btn-sm btn-outline-danger" style="border-radius: 20px;">
                                        <i class="bi bi-eye me-1"></i> Ver Detalhes
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/animacao.js"></script>

</body>

</html>
