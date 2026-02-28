<?php
session_start();
session_destroy(); // Mata todas as sessões ativas
header("Location: login.php"); // Manda de volta para a tela de login do admin
exit;
?>