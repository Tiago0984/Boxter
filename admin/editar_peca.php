<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];
$mensagem = "";

// 1. Busca os dados atuais da peça (incluindo as novas colunas)
$sql_busca = "SELECT * FROM tbl_pecas WHERE id_peca = $id";
$res_busca = mysqli_query($conn, $sql_busca);
$peca = mysqli_fetch_assoc($res_busca);

// 2. Lógica para Salvar Alterações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];

    // --- NOVOS CAMPOS PARA O FRETE ---
    $peso = $_POST['peso_peca']; // Singular conforme solicitado
    $comprimento = $_POST['comprimento_peca'];
    $altura = $_POST['altura_peca'];
    $largura = $_POST['largura_peca'];
    // ---------------------------------

    $nome_imagem = $peca['img_peca'];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nome_imagem = md5(uniqid()) . "." . $extensao;
        move_uploaded_file($_FILES['imagem']['tmp_name'], "../img/" . $nome_imagem);
    }

    // 3. UPDATE atualizado com as dimensões e o novo peso
    $sql_update = "UPDATE tbl_pecas SET 
                   nome_peca = '$nome', 
                   preco_venda_peca = '$preco', 
                   estoque_peca = '$estoque', 
                   img_peca = '$nome_imagem',
                   peso_peca = '$peso', 
                   comprimento_peca = '$comprimento', 
                   altura_peca = '$altura', 
                   largura_peca = '$largura' 
                   WHERE id_peca = $id";

    if (mysqli_query($conn, $sql_update)) {
        header("Location: estoque.php?status=editado");
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
    <title>Editar Peça - Boxter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-responsive.css">
    <style>
        /* 1. Fundo Preto Total */
        html,
        body {
            background-color: #000 !important;
            margin: 0;
            padding: 0;
            color: white;
        }

        /* 2. Menu Superior */
        .navbar {
            background-color: #000 !important;
            border-bottom: 2px solid #cd221f !important;
            padding: 15px 0;
        }

        /* 3. Estilo do Card (All Black) */
        .card-editar {
            background-color: #111 !important;
            border: 1px solid #333 !important;
            border-left: 4px solid #cd221f;
            padding: 30px;
            border-radius: 10px;
        }

        .form-control {
            background-color: #1a1a1a !important;
            border: 1px solid #444 !important;
            color: #fff !important;
        }

        .form-control:focus {
            border-color: #cd221f !important;
            box-shadow: none !important;
            background-color: #222 !important;
        }

        label {
            color: #ccc;
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .btn-danger {
            background-color: #cd221f !important;
            border: none !important;
            font-weight: bold;
        }

        .secao-frete {
            border-top: 1px solid #333;
            margin-top: 20px;
            padding-top: 15px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
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

    <div class="container pt-4">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <h2 class="mb-4 text-center">Editar Item: <span class="text-danger"><?php echo $peca['nome_peca']; ?></span></h2>

                <div class="card-editar">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $peca['id_peca']; ?>">

                        <div class="mb-3">
                            <label>Nome da Peça</label>
                            <input type="text" name="nome" class="form-control" value="<?php echo $peca['nome_peca']; ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label>Preço (R$)</label>
                                <input type="number" step="0.01" name="preco" class="form-control" value="<?php echo $peca['preco_venda_peca']; ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label>Qtd em Estoque</label>
                                <input type="number" name="estoque" class="form-control" value="<?php echo $peca['estoque_peca']; ?>" required>
                            </div>
                        </div>

                        <div class="secao-frete">
                            <p class="text-danger small fw-bold mb-3">LOGÍSTICA E DIMENSÕES</p>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label>Peso (kg)</label>
                                    <input type="number" step="0.001" name="peso_peca" class="form-control" value="<?php echo $peca['peso_peca']; ?>" placeholder="0.000">
                                </div>
                                <div class="col-6 mb-3">
                                    <label>Comprimento (cm)</label>
                                    <input type="number" step="0.1" name="comprimento_peca" class="form-control" value="<?php echo $peca['comprimento_peca']; ?>" placeholder="0.0">
                                </div>
                                <div class="col-6 mb-3">
                                    <label>Altura (cm)</label>
                                    <input type="number" step="0.1" name="altura_peca" class="form-control" value="<?php echo $peca['altura_peca']; ?>" placeholder="0.0">
                                </div>
                                <div class="col-6 mb-3">
                                    <label>Largura (cm)</label>
                                    <input type="number" step="0.1" name="largura_peca" class="form-control" value="<?php echo $peca['largura_peca']; ?>" placeholder="0.0">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 mt-2">
                            <label>Nova Imagem <small>(Opcional)</small></label>
                            <input type="file" name="imagem" class="form-control" accept="image/*">
                            <small class="text-muted">Atual: <?php echo $peca['img_peca']; ?></small>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">SALVAR ALTERAÇÕES</button>
                        <div class="text-center mt-3">
                            <a href="estoque.php" class="text-secondary text-decoration-none small">← Cancelar e voltar para o estoque</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
