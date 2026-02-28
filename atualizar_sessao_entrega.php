<?php
session_start();

// Recebe o JSON enviado pelo fetch
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    // Salva tudo na sessão para o processar_pagamento.php ler depois
    $_SESSION['entrega_temporaria'] = $data;
    
    // Retorna sucesso para o JavaScript
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Dados não recebidos']);
}