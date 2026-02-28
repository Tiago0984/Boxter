<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['admin_id'])) {
    exit;
}

$id = $_GET['id'];
$sql = "DELETE FROM tbl_pecas WHERE id_peca = $id";

if (mysqli_query($conn, $sql)) {
    header("Location: estoque.php?status=excluido");
} else {
    echo "Erro ao excluir.";
}
