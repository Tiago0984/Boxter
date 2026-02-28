<?php
session_start();

// 1. Captura o status que o JavaScript enviou via URL
$status_mp = $_GET['status'] ?? '';
$eh_cadastro = (isset($_GET['tipo']) && $_GET['tipo'] == 'cadastro');

// 2. Define os textos dinamicamente com base no status do Mercado Pago
if ($eh_cadastro) {
    $titulo    = "BEM-VINDO!";
    $subtitulo = "Seu cadastro foi realizado com sucesso.";
    $mensagem  = "Agora você já pode navegar e escolher as melhores peças para o seu veículo.";
} elseif ($status_mp == 'approved') {
    $titulo    = "OBRIGADO!";
    $subtitulo = "Seu pagamento foi processado com sucesso.";
    $mensagem  = "Estamos preparando suas peças. Em breve você receberá um e-mail com os detalhes do envio.";
} elseif ($status_mp == 'pending' || $status_mp == 'in_process') {
    $titulo    = "QUASE LÁ!";
    $subtitulo = "Seu pagamento está em análise.";
    $mensagem  = "Assim que o Mercado Pago confirmar a transação, enviaremos um e-mail com os detalhes.";
} else {
    // Caso o pagamento tenha sido recusado ou ocorra um erro
    $titulo    = "OPS!";
    $subtitulo = "Não conseguimos processar seu pagamento.";
    $mensagem  = "Por favor, verifique os dados do cartão ou tente outra forma de pagamento.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boxter - <?php echo $titulo; ?></title>

    <?php if ($status_mp == 'approved' || $eh_cadastro): ?>
        <meta http-equiv="refresh" content="10;url=index.php">
    <?php endif; ?>

    <link rel="stylesheet" href="css/estilo.css">
</head>

<style>
    body {
        margin: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        box-sizing: border-box;
    }

    .container-obrigado {
        background-color: #0d0d0d;
        color: #fff;
        border: 1px solid #cd221f;
        padding: clamp(24px, 4vw, 50px);
        border-radius: 15px;
        text-align: center;
        width: min(100%, 600px);
        margin: 0 auto;
        box-sizing: border-box;
    }

    .container-obrigado h1 {
        font-size: clamp(1.8rem, 4.8vw, 2.4rem);
        margin: 0 0 10px 0;
    }

    .container-obrigado h2 {
        font-size: clamp(1.2rem, 3.8vw, 1.7rem);
        margin: 0 0 16px 0;
    }

    .container-obrigado p {
        line-height: 1.5;
    }

    .btn-voltar {
        background-color: #cd221f;
        color: white;
        padding: 14px 24px;
        text-decoration: none;
        font-weight: bold;
        border-radius: 5px;
        display: inline-block;
        margin-top: 20px;
    }

    .timer-text {
        margin-top: 16px;
    }

    @media (max-width: 480px) {
        body {
            padding: 14px;
        }

        .container-obrigado {
            border-radius: 12px;
            padding: 20px 16px;
        }

        .btn-voltar {
            width: 100%;
            display: block;
            padding: 14px 16px;
            box-sizing: border-box;
        }
    }
</style>

<body>

    <div class="container-obrigado">
        <h1><?php echo $titulo; ?></h1>
        <h2><?php echo $subtitulo; ?></h2>
        <p><?php echo $mensagem; ?></p>

        <a href="index.php" class="btn-voltar">VOLTAR PARA A LOJA</a>

        <?php if ($status_mp == 'approved' || $eh_cadastro): ?>
            <p class="timer-text">Você será redirecionado em <span id="contagem">10</span> segundos...</p>
            <script>
                let tempo = 10;
                const span = document.getElementById('contagem');
                setInterval(() => {
                    tempo--;
                    if (tempo >= 0) span.innerText = tempo;
                }, 1000);
            </script>
        <?php endif; ?>
    </div>

</body>

</html>
