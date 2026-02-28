<?php include_once(__DIR__ . "/conexao.php"); ?>
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

    <main class="py-5" style="background-color: #0b0b0b; min-height: 80vh; display: flex; align-items: center; padding-top: 100px !important;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-5 col-lg-4">
                    <div class="card shadow-lg border-0" style="background-color: #1a1a1a; border-radius: 15px;">
                        <div class="card-body p-4 p-md-5">

                            <div class="text-center mb-4">
                                <i class="bi bi-person-lock" style="font-size: 3rem; color: #cd221f;"></i>
                                <h2 class="h4 mt-2" style="color: #ffffff; font-weight: bold; letter-spacing: 1px;">ACESSO CLIENTE</h2>
                            </div>

                            <form action="logica_login.php" method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label text-light small">E-mail cadastrado</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-light"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control bg-dark border-secondary text-white" id="email" placeholder="seu@email.com" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="senha" class="form-label text-light small">Sua senha</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-light"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="senha" class="form-control bg-dark border-secondary text-white" id="senha" placeholder="******" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-danger w-100 py-2 fw-bold text-uppercase" style="background-color: #cd221f; border: none; transition: 0.3s;">
                                    Entrar
                                </button>
                            </form>

                            <div class="mt-4 text-center border-top border-secondary pt-3 auth-switch">
                                <p class="small text-secondary mb-1">Ainda não é cliente?</p>
                                <a href="cadastro.php" class="text-decoration-none fw-bold auth-switch-link" style="color: #cd221f;">CADASTRAR AGORA</a>
                            </div>
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