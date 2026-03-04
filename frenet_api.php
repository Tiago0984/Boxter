<?php

/**
 * INTEGRAÇÃO COM API FRENET
 * Calcula fretes em tempo real para múltiplas transportadoras
 */

class FrenetAPI
{
    private string $token;
    private string $url_api = 'https://api.frenet.com.br/shipping/quote';

    /**
     * Dimensões padrão da embalagem (em cm)
     * AJUSTE CONFORME SUAS EMBALAGENS
     */
    private $dimensoes_padrao = [
        'altura' => 10,
        'largura' => 15,
        'comprimento' => 20,
    ];

    /**
     * Construtor recebe o token da Frenet via .env
     */
    public function __construct(string $token)
    {
        $this->token = trim($token);

        if (empty($this->token)) {
            throw new Exception('FRENET_TOKEN não configurado.');
        }
    }

    /**
     * Calcula frete para um determinado CEP
     * 
     * @param string $cep_destino CEP de entrega (apenas números)
     * @param float $peso_total Peso total em kg
     * @param float $valor_produto Valor do produto em R$
     * 
     * @return array Retorna array com shipping_services ou erro
     */
    public function calcularFrete(string $cep_destino, float $peso_total, float $valor_produto): array
    {
        // Validação básica
        $cep_destino = preg_replace('/\D/', '', $cep_destino);
        if (strlen($cep_destino) !== 8) {
            return ['error' => true, 'message' => 'CEP inválido'];
        }

        // Monta payload para Frenet
        $payload = [
            'ShippingServiceOrder' => [
                'Shipper' => [
                    'Token' => $this->token,
                    'RegisteryNumber' => '00000000000000', // CNPJ ou CPF da empresa (pode deixar assim para testes)
                ],
                'Receiver' => [
                    'Address' => [
                        'ZipCode' => $cep_destino,
                    ]
                ],
                'Items' => [
                    [
                        'ItemTypeID' => 2, // 2 = produto, 1 = serviço
                        'Description' => 'Peça Automotiva',
                        'Height' => $this->dimensoes_padrao['altura'],
                        'Width' => $this->dimensoes_padrao['largura'],
                        'Length' => $this->dimensoes_padrao['comprimento'],
                        'Weight' => number_format($peso_total, 2, '.', ''),
                        'ItemValue' => number_format($valor_produto, 2, '.', ''),
                    ]
                ]
            ]
        ];

        // Faz requisição à API
        $response = $this->fazerRequisicao($payload);

        return $response;
    }

    /**
     * Faz requisição HTTP POST à API Frenet
     */
    private function fazerRequisicao(array $payload): array
    {
        try {
            $ch = curl_init($this->url_api);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_error) {
                error_log("Frenet CURL Error: {$curl_error}");
                return ['error' => true, 'message' => 'Erro de conexão com Frenet'];
            }

            $dados = json_decode($response, true);

            if ($http_code !== 200) {
                $msg = $dados['Message'] ?? "Erro HTTP {$http_code}";
                error_log("Frenet API Error: {$msg}");
                return ['error' => true, 'message' => $msg];
            }

            return ['error' => false, 'data' => $dados];
        } catch (Exception $e) {
            error_log("Frenet Exception: " . $e->getMessage());
            return ['error' => true, 'message' => 'Erro ao calcular frete'];
        }
    }

    /**
     * Formata a resposta da Frenet para uso no carrinho
     */
    public function formatarOpcoes(array $response): array
    {
        if ($response['error']) {
            return [];
        }

        $opcoes = [];
        $shipping_services = $response['data']['ShippingServiceOrder']['ShippingServices'] ?? [];

        foreach ($shipping_services as $servico) {
            $opcoes[] = [
                'transportadora' => $servico['Name'] ?? 'Transportadora',
                'codigo' => $servico['ServiceCode'] ?? '',
                'prazo' => $servico['DeliveryTime'] ?? 0,
                'valor' => (float)($servico['ShippingCost'] ?? 0),
                'valor_formatado' => 'R$ ' . number_format($servico['ShippingCost'] ?? 0, 2, ',', '.'),
            ];
        }

        // Ordena por valor (menor primeiro)
        usort($opcoes, function ($a, $b) {
            return $a['valor'] <=> $b['valor'];
        });

        return $opcoes;
    }
}
