<?php
include_once "conexao.php";
session_start();

// Proteção: só acessa se estiver logado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login_cliente.php");
    exit();
}

$id_cliente = (int)$_SESSION['cliente_id'];
$sql = "SELECT * FROM tbl_clientes WHERE id_cliente = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_cliente);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$dados = mysqli_fetch_assoc($res);
?>
<!-- HTML 5 - Meu site -->
<!DOCTYPE html>
<!-- Sempre mudar para pt-br -->
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boxter Auto Peças - Especialista em Recuperação e Reparo</title>

    <!-- Descrição do site (SEO e compartilhamento) -->
    <meta name="description"
        content="Especialistas em reparação de peças automotivas com precisão de fábrica. Atendemos todas as marcas na Av. Marechal Tito, 1500.">

    <!-- Palavras-chave -->
    <meta name="keywords"
        content="oficina mecânica, reparo de peças, auto peças, recuperação de componentes, Boxter São Miguel">

    <!-- Autor do site -->
    <meta name="author" content="Equipe Codera Tech">

    <!-- Open Graph (Quando tiver um compartilhamento com nosso o Whats, Face, Linkedin) -->
    <meta property="og:title" content="Boxter Auto Peças - Especialista em Recuperação e Reparo">
    <meta property="og:description"
        content="Especialistas em reparação de peças automotivas com precisão de fábrica. Atendemos todas as marcas na Av. Marechal Tito, 1500.">
    <meta property="image" content="https://">
    <meta property="og:type" content="website">

    <!--Icon favicon e app-->
    <link rel="apple-touch-icon" sizes="57x57" href="img/icon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="img/icon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="img/icon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="img/icon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="img/icon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="img/icon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="img/icon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="img/icon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="img/icon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="img/icon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="img/icon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/icon/favicon-16x16.png">
    <link rel="manifest" href="img/icon/manifest.json">
    <meta name="msapplication-TileColor" content="#cd221f">
    <meta name="msapplication-TileImage" content="img/icon/ms-icon-144x144.png">
    <meta name="theme-color" content="#cd221f">

    <link rel="stylesheet" href="css/reset.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <link rel="stylesheet" type="text/css" href="css/slick.css" />
    <link rel="stylesheet" type="text/css" href="css/slick-theme.css" />

    <link rel="stylesheet" href="css/lity.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <link rel="stylesheet" href="css/estilo.css">

</head>

<body>

    <!--Inicio da pagina / Cabeçalho-->
    <?php require_once("conteudo/topo.php"); ?>
    <!-- Fim do cabeçalho -->

    <main class="main-content bg-black text-white" style="padding-top: 130px; min-height: 100vh; background-color: #000;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-11 col-md-8 col-lg-6">
                    <div class="card card-dados p-4 p-md-5 shadow-lg"
                        style="background-color: #0d0d0d; border: 1px solid #e4e4e4; border-radius: 15px;">

                        <h2 class="fw-bold mb-4 text-uppercase" style="color: #cd221f; letter-spacing: -1px;">
                            <i class="bi bi-person-vcard me-2"></i>MEUS DADOS
                        </h2>

                        <div class="row g-4">
                            <div class="col-12">
                                <p class="text-secondary small text-uppercase mb-1" style="letter-spacing: 1px;">Nome Completo</p>
                                <p class="info-value border-bottom border-secondary pb-2 fs-5 fw-semibold text-white">
                                    <?php echo $dados['nome_cliente']; ?>
                                </p>
                            </div>

                            <div class="col-12">
                                <p class="text-secondary small text-uppercase mb-1" style="letter-spacing: 1px;">E-mail de Acesso</p>
                                <p class="info-value border-bottom border-secondary pb-2 fs-5 text-white">
                                    <?php echo $dados['email_cliente']; ?>
                                </p>
                            </div>

                            <div class="col-12">
                                <p class="text-secondary small text-uppercase mb-1" style="letter-spacing: 1px;">CEP Cadastrado</p>
                                <p class="info-value fs-5 text-white">
                                    <?php echo $dados['cep_cliente']; ?>
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 pt-4 border-top border-secondary text-center">
                            <p class="small mb-3" style="color: #e4e4e4; opacity: 0.8;">
                                Deseja encerrar sua conta na plataforma?
                            </p>
                            <a href="excluir_conta.php" class="btn btn-outline-danger btn-sm px-4 rounded-pill fw-bold text-uppercase"
                                style="transition: all 0.3s ease;">
                                Apagar meu cadastro
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- RODAPE / FOOTER -->
    <?php require_once("conteudo/rodape.php") ?>
    <!-- FIM RODAPE / FOOTER -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script type="text/javascript" src="js/slick.js"></script>

    <script src="js/lity.min.js"></script>

    <script src="js/animacao.js"></script>

</body>

</html>