<?php
include_once "conexao.php";
session_start();

// O PHP procura pelos nomes 'email' e 'senha' que estão no seu HTML
if (isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha = $_POST['senha'];

    // Busca o usuário
    $sql = "SELECT * FROM tbl_clientes WHERE email_cliente = '$email'";
    $res = mysqli_query($conn, $sql);

    if ($res && mysqli_num_rows($res) > 0) {
        $usuario = mysqli_fetch_assoc($res);

        // Verifica a senha
        if (password_verify($senha, $usuario['senha_cliente'])) {
            $_SESSION['cliente_id']     = $usuario['id_cliente'];
            $_SESSION['cliente_nome']   = $usuario['nome_cliente'];
            $_SESSION['cliente_cep']    = $usuario['cep_cliente'];
            $_SESSION['cliente_email']  = $usuario['email_cliente'];

            // --- LINHA ESSENCIAL PARA O FRETE ---
            // Salva a UF na sessão para o carrinho saber se cobra R$ 15 ou R$ 35
            $_SESSION['cliente_estado'] = $usuario['uf_cliente'];

            header("Location: index.php");
            exit();
        } else {
            header("Location: login_cliente.php?erro=senha_incorreta");
            exit();
        }
    } else {
        header("Location: login_cliente.php?erro=usuario_nao_encontrado");
        exit();
    }
} else {
    echo "Nenhum dado enviado via POST. O PHP procurou por 'email' e não achou.";
}
