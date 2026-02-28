<?php
session_start();
require_once("../conexao.php");

$erro = "";
$mensagemLoginInvalido = "Usuario ou senha invalidos.";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = $mensagemLoginInvalido;
    } else {
        $sql = "SELECT * FROM tbl_usuarios WHERE email_usuario = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                $hashSenha = (string)($user['senha_usuario'] ?? '');

                if ($hashSenha !== '' && password_verify($senha, $hashSenha)) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $user['id_usuario'];
                    $_SESSION['admin_nome'] = $user['nome_usuario'];

                    header("Location: index.php");
                    exit;
                }
            } else {
                // Pequeno atraso para reduzir tentativas automatizadas.
                usleep(250000);
            }

            mysqli_stmt_close($stmt);
            $erro = $mensagemLoginInvalido;
        } else {
            error_log('Falha ao preparar consulta de login admin.');
            $erro = "Nao foi possivel processar o login agora.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boxter Admin - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-responsive.css">
    <style>
        body {
            background-color: #0b0b0b;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #1a1a1a;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #cd221f;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 25px rgba(205, 34, 31, 0.2);
        }

        .form-control {
            background-color: #222;
            border: 1px solid #444;
            color: #fff;
        }

        .form-control:focus {
            background-color: #252525;
            border-color: #cd221f;
            color: #fff;
            box-shadow: none;
        }

        .btn-danger {
            background-color: #cd221f;
            border: none;
            font-weight: bold;
            padding: 12px;
        }

        .text-secondary:hover {
            color: #cd221f !important;
        }
    </style>
</head>

<body>
    <div class="container d-flex flex-column align-items-center justify-content-center vh-100">

        <div class="mb-3" style="width: 100%; max-width: 400px;">
            <a href="../index.php" class="text-secondary text-decoration-none small" style="transition: 0.3s;">
                <i class="bi bi-arrow-left"></i> Voltar para o Site
            </a>
        </div>

        <div class="login-card">
            <div class="text-center mb-4">
                <h2 class="text-danger fw-bold">BOXTER ADMIN</h2>
                <p class="text-secondary small">Painel de Gerenciamento</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger py-2 small text-center"><?php echo $erro; ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="text-light small">E-mail</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@boxter.com" required>
                </div>
                <div class="mb-4">
                    <label class="text-light small">Senha</label>
                    <input type="password" name="senha" class="form-control" placeholder="********" required>
                </div>
                <button type="submit" class="btn btn-danger w-100">ENTRAR NO SISTEMA</button>
            </form>
        </div>
    </div>
</body>

</html>
