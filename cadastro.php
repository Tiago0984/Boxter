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
<style>
    /* Estilo para manter o padrão dark nos campos automáticos do CEP */
    #cidade,
    #uf {
        background-color: #121212 !important;
        color: #cd221f !important;
        /* Texto em vermelho para destacar que foi preenchido */
        font-weight: bold;
    }
</style>

<body>

    <?php require_once("conteudo/topo.php"); ?>
    <main class="py-5" style="background-color: #0b0b0b; min-height: 90vh; display: flex; align-items: center; padding-top: 110px !important;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="card shadow-lg border-0" style="background-color: #1a1a1a; border-radius: 15px;">
                        <div class="card-body p-4 p-md-5">

                            <div class="text-center mb-4">
                                <i class="bi bi-person-plus-fill" style="font-size: 3rem; color: #cd221f;"></i>
                                <h2 class="h4 mt-2" style="color: #ffffff; font-weight: bold; letter-spacing: 1px;">CRIAR CONTA BOXTER</h2>
                                <p class="text-secondary small">Preencha os dados abaixo para agilizar suas compras e fretes.</p>
                            </div>

                            <form action="processa_cadastro.php" method="POST">
                                <div class="row">
                                    <div class="col-md-5 border-md-end border-secondary">
                                        <h5 class="text-light mb-3" style="font-size: 1rem; border-left: 3px solid #cd221f; padding-left: 10px;">Dados de Acesso</h5>
                                        <div class="mb-3">
                                            <label class="form-label text-light small">Nome Completo</label>
                                            <input type="text" name="nome_cliente" class="form-control bg-dark border-secondary text-white" placeholder="Seu nome" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-light small">E-mail</label>
                                            <input type="email" name="email_cliente" class="form-control bg-dark border-secondary text-white" placeholder="seu@email.com" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-light small">Telefone</label>
                                            <input type="tel" name="telefone_cliente" class="form-control bg-dark border-secondary text-white" placeholder="(11) 99999-9999" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label text-light small">Senha</label>
                                            <input type="password" name="senha_cliente" class="form-control bg-dark border-secondary text-white" placeholder="******" required>
                                        </div>
                                    </div>

                                    <div class="col-md-7 ps-md-4">
                                        <h5 class="text-light mb-3" style="font-size: 1rem; border-left: 3px solid #cd221f; padding-left: 10px;">Endereço de Entrega</h5>

                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label text-light small">CEP (Automático)</label>
                                                <input type="text" name="cep_cliente" id="cep" class="form-control bg-dark border-secondary text-white" placeholder="00000-000" maxlength="9" inputmode="numeric" onblur="buscaCEP()" required>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label text-light small">Bairro</label>
                                                <input type="text" name="bairro_cliente" id="bairro" class="form-control bg-dark border-secondary text-white" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-light small">Logradouro / Rua</label>
                                            <input type="text" name="endereco_cliente" id="logradouro" class="form-control bg-dark border-secondary text-white" placeholder="Rua, Av..." required>
                                        </div>

                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <label class="form-label text-light small">Número</label>
                                                <input type="text" name="numero_cliente" id="numero" class="form-control bg-dark border-secondary text-white" required>
                                            </div>
                                            <div class="col-8 mb-3">
                                                <label class="form-label text-light small">Complemento</label>
                                                <input type="text" name="complemento_cliente" class="form-control bg-dark border-secondary text-white" placeholder="Apto, Bloco...">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <label class="form-label text-light small">Cidade</label>
                                                <input type="text" name="cidade_cliente" id="cidade" class="form-control bg-dark border-secondary text-white" required>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label class="form-label text-light small">UF</label>
                                                <input type="text" name="uf_cliente" id="uf" class="form-control bg-dark border-secondary text-white" required maxlength="2">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-danger w-100 py-3 fw-bold text-uppercase" style="background-color: #cd221f; border: none; letter-spacing: 2px;">
                                        Finalizar meu Cadastro
                                    </button>
                                </div>
                            </form>

                            <div class="mt-4 text-center auth-switch">
                                <p class="small text-secondary mb-0">Já possui uma conta?</p>
                                <a href="login_cliente.php" class="text-decoration-none fw-bold auth-switch-link" style="color: #cd221f;">FAZER LOGIN</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once("conteudo/rodape.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="js/slick.js"></script>
    <script src="js/lity.min.js"></script>

    <script src="js/animacao.js"></script>



</body>

</html>