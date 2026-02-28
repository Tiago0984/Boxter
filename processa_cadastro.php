<?php
include_once "conexao.php";

function limpar_texto_formulario($valor): string
{
    return trim((string)preg_replace('/^\s*string\s*:\s*/i', '', (string)$valor));
}

// 1. Recebe os dados do formulario
$nome         = limpar_texto_formulario($_POST['nome_cliente'] ?? '');
$email        = trim((string)($_POST['email_cliente'] ?? ''));
$telefone     = trim((string)($_POST['telefone_cliente'] ?? ''));
$senhaRaw     = (string)($_POST['senha_cliente'] ?? '');
$senha        = password_hash($senhaRaw, PASSWORD_DEFAULT);
$cepBruto     = (string)($_POST['cep_cliente'] ?? '');
$cep          = preg_replace('/\D+/', '', $cepBruto);
$endereco     = limpar_texto_formulario($_POST['endereco_cliente'] ?? '');
$numero       = limpar_texto_formulario($_POST['numero_cliente'] ?? '');
$complemento  = limpar_texto_formulario($_POST['complemento_cliente'] ?? '');
$bairro       = limpar_texto_formulario($_POST['bairro_cliente'] ?? '');
$cidade       = limpar_texto_formulario($_POST['cidade_cliente'] ?? '');
$uf           = strtoupper(limpar_texto_formulario($_POST['uf_cliente'] ?? ''));
$uf           = substr((string)preg_replace('/[^A-Z]/', '', $uf), 0, 2);

if (strlen($cep) === 8) {
    $cep = substr($cep, 0, 5) . '-' . substr($cep, 5);
}

if ($telefone === '') {
    die("O campo telefone e obrigatorio.");
}

if ($nome === '' || $email === '' || $senhaRaw === '' || $cep === '' || $endereco === '' || $numero === '' || $bairro === '' || $cidade === '' || $uf === '') {
    die("Preencha todos os campos obrigatorios.");
}

// 2. Insere no banco
$sql = "INSERT INTO tbl_clientes (
            nome_cliente, email_cliente, telefone_cliente, senha_cliente,
            cep_cliente, endereco_cliente, numero_cliente,
            complemento_cliente, bairro_cliente, cidade_cliente,
            uf_cliente, status_cliente
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo')";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssss",
        $nome,
        $email,
        $telefone,
        $senha,
        $cep,
        $endereco,
        $numero,
        $complemento,
        $bairro,
        $cidade,
        $uf
    );

    if (mysqli_stmt_execute($stmt)) {
        $cliente_id = mysqli_insert_id($conn);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['cliente_id']     = $cliente_id;
        $_SESSION['cliente_nome']   = $nome;
        $_SESSION['cliente_email']  = $email;
        $_SESSION['cliente_cep']    = $cep;
        $_SESSION['cliente_estado'] = $uf;

        header("Location: obrigado.php?tipo=cadastro");
        exit();
    } else {
        echo "Erro ao executar: " . mysqli_error($conn);
    }
} else {
    echo "Erro na preparacao: " . mysqli_error($conn);
}
