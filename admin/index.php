<?php
session_start();

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Conecta ao banco de dados
include_once "../conexao.php";

// 3. Busca apenas o total de pedidos (uma consulta simples)
$res_pedidos = mysqli_query($conn, "SELECT COUNT(*) as qtd FROM tbl_pedido");
$dados_pedidos = mysqli_fetch_assoc($res_pedidos);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Boxter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="admin-responsive.css">
    <style>
        /* 1. Fundo Preto Total */
        html,
        body {
            background-color: #000 !important;
            margin: 0;
            padding: 0;
            color: white;
            min-height: 100vh;
            /* Garante que o fundo não fique branco no rodapé */
        }

        /* 2. Menu Superior (Padronizado com respiro de 15px) */
        .navbar {
            background-color: #000 !important;
            margin-bottom: 0 !important;
            border-bottom: 2px solid #cd221f !important;
            padding: 15px 0 !important;
        }

        /* Seleção direta para funcionar na sua div d-flex */
        .navbar .nav-link {
            color: #ffffff !important;
            text-decoration: none !important;
            background: none !important;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #0056b3 !important;
            background: none !important;
        }

        /* 3. Cards do Painel (Para o index.php) */
        .card-dash {
            background: #111;
            border: 1px solid #333;
            border-left: 4px solid #cd221f;
            border-radius: 10px;
            padding: 30px;
            transition: transform 0.3s ease;
            color: white;
        }

        .card-dash:hover {
            transform: translateY(-5px);
            border-color: #cd221f;
        }

        /* Botão Sair padrão */
        .btn-outline-danger {
            border-color: #cd221f;
            color: #cd221f;
        }

        .btn-outline-danger:hover {
            background-color: #cd221f;
            color: white;
        }

        /* Tabela (Para o pedidos.php) */
        .tabela-boxter {
            background-color: #111;
            border: 1px solid #333;
            color: #fff;
        }

        .cabecalho-vermelho {
            background-color: #cd221f !important;
            color: #fff !important;
            text-align: center;
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
        <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
            <h2 class="text-white">
                <i class="bi bi-speedometer2" style="color: #cd221f;"></i> Painel Boxter
            </h2>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-dash text-center">
                    <h5 class="text-secondary small text-uppercase">Total de Pedidos</h5>
                    <h2 class="display-4 fw-bold"><?php echo $dados_pedidos['qtd'] ?? 0; ?></h2>
                    <a href="pedidos.php" class="btn btn-danger btn-sm mt-3">Ver Todos</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-dash text-center">
                    <i class="bi bi-tools display-6 mb-2" style="color: #cd221f;"></i>
                    <h5>Gerenciar Peças</h5>
                    <p class="text-secondary small">Adicione ou edite o estoque.</p>
                    <a href="estoque.php" class="btn btn-outline-light btn-sm">Ir para Estoque</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
