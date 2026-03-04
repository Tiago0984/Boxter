<?php

/**
 * ENDPOINT PARA CALCULAR FRETE VIA FRENET
 */

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=UTF-8');

include_once 'frenet_api.php';

$log_file = __DIR__ . '/log_frenet.txt';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido', 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['cep']) || !isset($input['peso'])) {
        throw new Exception('CEP e peso são obrigatórios', 400);
    }

    $cep = preg_replace('/\D/', '', $input['cep']);
    $peso = (float)$input['peso'];
    $valor_produtos = (float)($input['valor_produtos'] ?? 0);

    if (strlen($cep) !== 8) {
        throw new Exception('CEP inválido', 400);
    }

    $token = trim((string)(getenv('FRENET_TOKEN') ?: ''));
    if ($token === '') {
        $env = parse_ini_file(__DIR__ . '/.env');
        $token = trim((string)($env['FRENET_TOKEN'] ?? ''));
    }
    if ($token === '') {
        throw new Exception('FRENET_TOKEN não configurado.');
    }

    $frenet = new FrenetAPI($token);

    $response = $frenet->calcularFrete($cep, $peso, $valor_produtos);

    if ($response['error']) {
        throw new Exception($response['message']);
    }

    $opcoes = $frenet->formatarOpcoes($response);

    echo json_encode([
        'success' => true,
        'fallback' => false,
        'opcoes' => $opcoes,
    ]);
    exit;
} catch (Exception $e) {

    error_log("Erro Frenet: " . $e->getMessage(), 3, $log_file);

    // Fallback automático
    $opcoes_fallback = gerarOpcoesFreteAutomatico($peso ?? 1, $valor_produtos ?? 0);

    $status = (int)$e->getCode();
    if ($status < 400 || $status >= 600) {
        $status = 500;
    }
    http_response_code($status);

    echo json_encode([
        'success' => false,
        'fallback' => true,
        'message' => $e->getMessage(),
        'opcoes' => $opcoes_fallback,
    ]);
    exit;
}

/**
 * Fallback automático
 */
function gerarOpcoesFreteAutomatico(float $peso, float $valor_produtos): array
{
    $frete_base = 15.00;
    $valor_por_kg = 3.50;

    $valor_calculado = $frete_base + ($peso * $valor_por_kg);
    $valor_calculado = max(12.90, $valor_calculado);
    $valor_calculado = min(89.90, $valor_calculado);

    $valor_calculado = ceil($valor_calculado * 10) / 10;
    $valor_calculado = floor($valor_calculado) + 0.90;

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
