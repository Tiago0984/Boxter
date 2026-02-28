<?php
session_start();
session_destroy(); // Mata todas as variáveis de login
header("Location: index.php");
exit();
?>