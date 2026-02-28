<?php include_once(__DIR__ . "/conexao.php"); ?>
<?php

$ok = 0;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

try {

    if (isset($_POST["email"])) {
        // SÓ VAI ENTRAR AQUI... SE PREENCHER O FORM E CLICAR NO BOTÃO ENVIAR
        require 'vendor/phpmailer/Exception.php';
        require 'vendor/phpmailer/PHPMailer.php';
        require 'vendor/phpmailer/SMTP.php';

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(exceptions: true);

        $nome        = $_POST["nome"];
        $email       = $_POST["email"];
        $fone        = $_POST["telefone"];
        $msg         = $_POST["mensagem"];
        $motivo      = $_POST["motivo"];
        $assunto     = "E-mail do site Boxter Auto Pecas - " . $motivo;

        // var_dump(value: $nome);
        // var_dump(value: $email);
        // var_dump(value: $fone);
        // var_dump(value: $msg);
        // var_dump(value: $motivo);

        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Ativar saída de debug detalhada
        $mail->isSMTP();                                            //Enviar via SMTP
        $mail->Host       = 'tipi05.360criativo.com.br';            //Definir o servidor SMTP para envio
        $mail->SMTPAuth   = true;                                   //Ativar autenticação SMTP
        $mail->Username   = 'contato@tipi05.360criativo.com.br';    //Nome de usuário SMTP
        $mail->Password   = 'TIPI05**2025';                         //Senha SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Ativar criptografia TLS implícita
        $mail->Port       = 465;                                    //Porta TCP para conexão; use 587 se você configurou

        //Recipients
        $mail->setFrom('contato@tipi05.360criativo.com.br', $assunto);         // QUEM DISPARA O EMAIL
        $mail->addAddress('collinpool2012@gmail.com', 'Tiago');                //Adicionar um destinatário    
        // $mail->addAddress('ellen@example.com');                                //O nome é opcional
        // $mail->addReplyTo('info@example.com', 'Information');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         //Adicionar anexos
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Nome opcional

        //Content
        $mail->isHTML(true);                                  //Definir formato de email como HTML
        $mail->Subject = $assunto;
        $mail->Body    = "
            Nome: $nome <br>
            E-mail: $email <br>
            Telefone: $fone <br>
            Mensagem: $msg <br>
            Motivo: $motivo         
        ";

        $mail->AltBody = "
            Nome: $nome /n
            E-mail: $email /n
            Telefone: $fone /n
            Mensagem: $msg /n
            Motivo: $motivo         
        ";

        $mail->send();
        // echo $nome . ', Sua mensagem foi enviada com sucesso!';
        $ok = 1;
    } // FIM DO IF

} catch (Exception $e) {
    // echo $nome .  ", Não foi possível o envio do e-mail: {$mail->ErrorInfo}";
    $ok = 2;
}

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

    <main>
        <!-- Inicio: MAPA -->
        <?php require_once("conteudo/pg-contato-mapa.php") ?>
        <!-- Fim do MAPA -->

        <!-- Inicio diferencial -->
        <?php require_once("conteudo/diferencial.php") ?>
        <!-- Fim diferencial -->

        <!-- INICIO FORM DE CONTATO -->
        <?php require_once("conteudo/pg-contato-formulario.php") ?>
        <!-- FIM FORM DE CONTATO -->

        <!-- Inicio MARCAS -->
        <?php require_once("conteudo/marcas.php") ?>
        <!-- Fim MARCAS -->

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