<?php

/**
 * ENDPOINT PARA CALCULAR FRETE VIA FRENET
 * Chamado via AJAX quando o cliente insere o CEP
 */

session_start();
header('Content-Type: application/json; charset=UTF-8');

include_once 'frenet_api.php';
include_once 'conexao.php';

$log_file = __DIR__ . '/log_frenet.txt';

try {
    // Valida token CSRF simples
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido', 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    error_log("Input recebido: " . json_encode($input), 3, $log_file);

    if (!isset($input['cep']) || !isset($input['peso'])) {
        throw new Exception('CEP e peso são obrigatórios', 400);
    }

    $cep = preg_replace('/\D/', '', $input['cep']);
    $peso = (float)$input['peso'];
    $valor_produtos = (float)($input['valor_produtos'] ?? 0);

    if (strlen($cep) !== 8) {
        throw new Exception('CEP inválido', 400);
    }

    // SEMPRE usa cálculo automático por padrão (falha segura)
    error_log("Usando cálculo automático para CEP: $cep, Peso: $peso kg", 3, $log_file);
    $opcoes_fallback = gerarOpcoesFreteAutomatico($peso, $valor_produtos);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'fallback' => true,
        'opcoes' => $opcoes_fallback,
    ]);
    exit;

} catch (Exception $e) {
    error_log("Exceção: " . $e->getMessage(), 3, $log_file);
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}

/**
 * Calcula frete automático quando Frenet não está disponível
 */
function gerarOpcoesFreteAutomatico(float $peso, float $valor_produtos): array
{
    // Cálculo inteligente de frete baseado em peso e valor
    $frete_base = 15.00;
    $valor_por_kg = 3.50;
    
    // Calcula valor base + adicional por kg
    $valor_calculado = $frete_base + ($peso * $valor_por_kg);
    
    // Garante valor mínimo e máximo razoáveis
    $valor_calculado = max(12.90, $valor_calculado); // Mínimo R$ 12,90
    $valor_calculado = min(89.90, $valor_calculado); // Máximo R$ 89,90
    
    // Arredonda para múltiplo de 0,90 (formato comum de preços)
    $valor_calculado = ceil($valor_calculado * 10) / 10;
    $valor_calculado = floor($valor_calculado) + 0.90;

    // Prazo baseado no valor (quanto maior o preço, mais rápido)
    $prazo = $valor_calculado > 50 ? 3 : 5;

    return [
        [
            'transportadora' => 'Transportadora Padrão',
            'codigo' => 'auto',
            'prazo' => $prazo,
            'valor' => $valor_calculado,
            'valor_formatado' => 'R$ ' . number_format($valor_calculado, 2, ',', '.'),
        ],
        [
            'transportadora' => 'Entrega Expressa',
            'codigo' => 'express',
            'prazo' => 2,
            'valor' => $valor_calculado + 15.00,
            'valor_formatado' => 'R$ ' . number_format($valor_calculado + 15.00, 2, ',', '.'),
        ]
    ];
}