<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];

    // --- NOVOS CAMPOS PARA FRETE ---
    $peso = $_POST['peso_peca'];
    $comprimento = $_POST['comprimento_peca'];
    $altura = $_POST['altura_peca'];
    $largura = $_POST['largura_peca'];
    // -------------------------------

    // INSERT atualizado com todas as colunas de logística
    $sql = "INSERT INTO tbl_pecas (nome_peca, preco_venda_peca, estoque_peca, peso_peca, comprimento_peca, altura_peca, largura_peca) 
            VALUES ('$nome', '$preco', '$estoque', '$peso', '$comprimento', '$altura', '$largura')";

    if (mysqli_query($conn, $sql)) {
        $mensagem = "sucesso";
    } else {
        $mensagem = "erro";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Item - Boxter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-responsive.css">
    <style>
        /* 1. Fundo Preto Total (Igual ao Estoque) */
        html,
        body {
            background-color: #000 !important;
            margin: 0;
            padding: 0;
            color: white;
        }

        /* 2. Menu Superior Padronizado */
        .navbar {
            background-color: #000 !important;
            border-bottom: 2px solid #cd221f !important;
            padding: 15px 0;
        }

        /* 3. Estilo do Formulário (All Black) */
        .card-boxter {
            background: #111;
            border: 1px solid #333;
            border-left: 4px solid #cd221f;
            padding: 30px;
            border-radius: 10px;
        }

        .form-control {
            background: #1a1a1a;
            border: 1px solid #333;
            color: white;
        }

        .form-control:focus {
            background: #222;
            border-color: #cd221f;
            color: white;
            box-shadow: none;
        }

        label {
            color: #bbb;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .btn-danger {
            background-color: #cd221f;
            border: none;
            font-weight: bold;
        }

        .secao-logistica {
            border-top: 1px solid #333;
            margin-top: 20px;
            padding-top: 15px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark mb-4 border-bottom border-danger">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger" href="index.php">BOXTER ADMIN</a>
            <div class="d-flex">
                <a href="index.php" class="nav-link text-white me-3">Início</a>
                <a href="estoque.php" class="nav-link text-white me-3">Estoque</a>
                <a href="pedidos.php" class="nav-link text-white me-4">Vendas</a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">Sair</a>
            </div>
        </div>
    </nav>
    <div class="container pt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Cadastrar Peça</h2>

                <?php if ($mensagem == "sucesso"): ?>
                    <div class="alert alert-success">Peça salva! <a href="estoque.php">Ver Estoque</a></div>
                <?php endif; ?>

                <div class="card-boxter">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="small">Nome do Item</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="small">Preço (R$)</label>
                                <input type="number" step="0.01" name="preco" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="small">Quantidade</label>
                                <input type="number" name="estoque" class="form-control" required>
                            </div>
                        </div>

                        <div class="secao-logistica">
                            <p class="text-danger small fw-bold mb-3">DADOS DE ENVIO (FRETE)</p>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="small">Peso (kg)</label>
                                    <input type="number" step="0.001" name="peso_peca" class="form-control" placeholder="Ex: 1.500" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="small">Comprimento (cm)</label>
                                    <input type="number" step="0.01" name="comprimento_peca" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="small">Altura (cm)</label>
                                    <input type="number" step="0.01" name="altura_peca" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="small">Largura (cm)</label>
                                    <input type="number" step="0.01" name="largura_peca" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 mt-2">
                            <label class="small text-secondary">Imagem da Peça</label>
                            <input type="file" name="imagem" class="form-control" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold mt-2">GRAVAR PEÇA</button>
                    </form>
                </div>
                <div class="text-center mt-3">
                    <a href="estoque.php" class="text-secondary">Voltar para a lista</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
