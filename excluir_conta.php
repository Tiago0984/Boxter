<?php
include_once "conexao.php";
session_start();

if (isset($_SESSION['cliente_id'])) {
    $id = $_SESSION['cliente_id'];
    // Deleta o cliente do banco
    $sql = "DELETE FROM tbl_clientes WHERE id_cliente = $id";
    
    if (mysqli_query($conn, $sql)) {
        session_destroy(); // Loga fora automaticamente
        header("Location: index.php?msg=conta_excluida");
        exit();
    }
}
?>