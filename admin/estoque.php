<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once("../conexao.php");

$sql = "SELECT id_peca, nome_peca, preco_venda_peca, estoque_peca FROM tbl_pecas ORDER BY nome_peca ASC";
$res = mysqli_query($conn, $sql);

if (!$res) {
    die("Erro no banco: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque Boxter - Painel Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="admin-responsive.css">

    <style>
        html,
        body {
            background-color: #000 !important;
            margin: 0;
            padding: 0;
            color: white !important;
            min-height: 100vh;
        }

        .navbar {
            background-color: #000 !important;
            border-bottom: 2px solid #cd221f !important;
            padding: 15px 0;
        }

        .navbar .nav-link {
            color: #ffffff !important;
            text-decoration: none !important;
            transition: color 0.3s ease-in-out !important;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #0056b3 !important;
            background: none !important;
        }

        .tabela-boxter {
            background-color: #111 !important;
            border: 1px solid #333 !important;
            color: #fff !important;
        }

        .cabecalho-vermelho {
            background-color: #cd221f !important;
            color: #fff !important;
        }

        .tabela-boxter td {
            border-color: #333 !important;
            vertical-align: middle;
            text-align: center;
        }

        .estoque-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .estoque-tabela td {
            vertical-align: middle;
        }

        @media (max-width: 991px) {
            .estoque-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 767.98px) {
            .estoque-tabela {
                min-width: 100% !important;
            }

            .table-responsive {
                overflow: visible;
            }

            .estoque-tabela thead {
                display: none;
            }

            .estoque-tabela,
            .estoque-tabela tbody,
            .estoque-tabela tr,
            .estoque-tabela td {
                display: block;
                width: 100%;
            }

            .estoque-tabela tbody tr {
                background-color: #111;
                border: 1px solid #333;
                border-radius: 10px;
                margin-bottom: 10px;
                overflow: hidden;
            }

            .estoque-tabela tbody td {
                text-align: left !important;
                padding: 10px 14px !important;
                border: 0;
                border-bottom: 1px solid #2a2a2a !important;
                white-space: normal;
            }

            .estoque-tabela tbody td:last-child {
                border-bottom: 0 !important;
            }

            .estoque-tabela tbody td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 2px;
                font-size: 0.72rem;
                font-weight: 700;
                color: #9aa0a6;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .estoque-tabela .acoes-cell .btn {
                width: 100%;
                margin: 0 0 8px 0 !important;
            }

            .estoque-tabela .acoes-cell .btn:last-child {
                margin-bottom: 0 !important;
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

    <div class="container pt-5">
        <div class="estoque-header">
            <h2 class="text-white">
                <i class="bi bi-box-seam" style="color: #cd221f;"></i> Estoque
            </h2>
            <a href="cadastrar_peca.php" class="btn btn-danger" style="background-color: #cd221f; border: none;">+ Nova Peça</a>
        </div>

        <div class="table-responsive">
            <table class="table tabela-boxter table-dark table-hover estoque-tabela">
                <thead class="cabecalho-vermelho">
                    <tr>
                        <th class="text-center">Cód.</th>
                        <th>Peça</th>
                        <th class="text-center">Preço</th>
                        <th class="text-center">Qtd</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($peca = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td data-label="Cod." class="align-middle text-center">#<?php echo $peca['id_peca']; ?></td>
                            <td data-label="Peca" class="align-middle"><?php echo $peca['nome_peca']; ?></td>
                            <td data-label="Preco" class="align-middle text-center">R$ <?php echo number_format($peca['preco_venda_peca'], 2, ',', '.'); ?></td>
                            <td data-label="Qtd" class="align-middle text-center"><?php echo $peca['estoque_peca']; ?> un</td>

                            <td data-label="Acoes" class="text-center acoes-cell">
                                <a href="editar_peca.php?id=<?php echo $peca['id_peca']; ?>"
                                    class="btn btn-outline-warning btn-sm px-3 me-2">
                                    Editar
                                </a>

                                <a href="excluir_peca.php?id=<?php echo $peca['id_peca']; ?>"
                                    class="btn btn-danger btn-sm px-3"
                                    onclick="return confirm('Cuidado! Deseja mesmo excluir esta peça?')">
                                    Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
