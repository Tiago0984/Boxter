<?php
// 1. Configurações de erro e Sessão
ini_set('display_errors', 0);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// 2. Carrega as dependências e a Conexão Centralizada
include_once(__DIR__ . "/conexao.php");

if (!isset($_SESSION['cliente_id'])) {
  header("Location: login_cliente.php");
  exit;
}

if (empty($_SESSION['carrinho']) || !is_array($_SESSION['carrinho'])) {
  die("Erro: O seu carrinho esta vazio.");
}


$items = [];
$subtotal_produtos = 0;
$peso_total = 0;

// 4. Montagem dos itens do Carrinho e Cálculo do Peso
if (!empty($_SESSION['carrinho'])) {
  $stmt_peca = $conn->prepare("SELECT nome_peca, preco_venda_peca, peso_peca FROM tbl_pecas WHERE id_peca = ?");
  if ($stmt_peca) {
    foreach ($_SESSION['carrinho'] as $id => $qtd) {
      $id_limpo = (int)$id;
      $qtd = (int)$qtd;
      if ($id_limpo <= 0 || $qtd <= 0) {
        continue;
      }

      $stmt_peca->bind_param('i', $id_limpo);
      $stmt_peca->execute();
      $res = $stmt_peca->get_result();
      $p = $res ? $res->fetch_assoc() : null;

      if ($p && $p['preco_venda_peca'] > 0) {
        $subtotal_produtos += (float)$p['preco_venda_peca'] * (int)$qtd;
        $peso_total += (float)$p['peso_peca'] * (int)$qtd; // Soma o peso para o frete

        $items[] = [
          "title" => (string)$p['nome_peca'],
          "quantity" => (int)$qtd,
          "unit_price" => (float)$p['preco_venda_peca']
        ];
      }
    }
    $stmt_peca->close();
  }
}

// 5. Cálculo do Frete Dinâmico (Priorizando o Endereço de Entrega)
// 5. Cálculo do Frete Dinâmico (CONSISTENTE COM O CARRINHO)
$valor_frete = 0;

// Se tem dados de frete da sessão, usa esses valores
if (isset($_SESSION['frete_valor']) && $_SESSION['frete_valor'] > 0) {
  $valor_frete = (float)$_SESSION['frete_valor'];
}
// Se não, calcula automaticamente igual ao carrinho
else {
  // Cálculo idêntico ao que está no calcular_frete_frenet.php
  $frete_base = 15.00;
  $valor_por_kg = 3.50;

  // Calcula valor base + adicional por kg
  $valor_calculado = $frete_base + ($peso_total * $valor_por_kg);

  // Garante valor mínimo e máximo razoáveis
  $valor_calculado = max(12.90, $valor_calculado); // Mínimo R$ 12,90
  $valor_calculado = min(89.90, $valor_calculado); // Máximo R$ 89,90

  // Arredonda para múltiplo de 0,90 (formato comum de preços)
  $valor_calculado = ceil($valor_calculado * 10) / 10;
  $valor_calculado = floor($valor_calculado) + 0.90;

  $valor_frete = $valor_calculado;
}

// Adiciona o Frete como um item no Mercado Pago para o cliente ver o custo
if ($valor_frete > 0) {
  $items[] = [
    "title" => "Custo de Envio (Frete)",
    "quantity" => 1,
    "unit_price" => (float)$valor_frete
  ];
}

$valor_total = $subtotal_produtos + $valor_frete;

$_SESSION['valor_frete'] = $valor_frete;
$_SESSION['subtotal_produtos'] = $subtotal_produtos;
$_SESSION['valor_total_final'] = $valor_total;

// Bloqueio de segurança
if ($valor_total <= 0) {
  die("Erro: O seu carrinho esta vazio.");
}

$email_cliente = $_SESSION['cliente_email'] ?? "comprador_teste@testuser.com";
$modo_pagamento = strtolower((string)(getenv('APP_PAYMENT_MODE') ?: 'sandbox'));
$modos_validos = ['sandbox', 'production', 'test_simulated'];
if (!in_array($modo_pagamento, $modos_validos, true)) {
  $modo_pagamento = 'sandbox';
}

$public_key_mercadopago = '';
$pk_test = trim((string)(getenv('MP_PUBLIC_KEY_TEST') ?: ''));
$pk_prod = trim((string)(getenv('MP_PUBLIC_KEY_PROD') ?: ''));
$pk_generica = trim((string)(getenv('MP_PUBLIC_KEY') ?: ''));

if ($modo_pagamento === 'production') {
  if ($pk_prod !== '' && stripos($pk_prod, 'TEST-') !== 0) {
    $public_key_mercadopago = $pk_prod;
  } elseif ($pk_generica !== '' && stripos($pk_generica, 'TEST-') !== 0) {
    $public_key_mercadopago = $pk_generica;
  }
} else {
  // Em sandbox, aceitamos apenas chave de teste para evitar uso acidental de credencial live.
  if ($pk_test !== '' && stripos($pk_test, 'TEST-') === 0) {
    $public_key_mercadopago = $pk_test;
  } elseif ($pk_generica !== '' && stripos($pk_generica, 'TEST-') === 0) {
    $public_key_mercadopago = $pk_generica;
  }
}

if ($modo_pagamento !== 'test_simulated' && $public_key_mercadopago === '') {
  die('Configuracao Mercado Pago invalida para este ambiente. Verifique MP_PUBLIC_KEY_TEST (sandbox) ou MP_PUBLIC_KEY_PROD (producao).');
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#000000">
  <title>Pagamento Seguro - Boxter Auto Peças</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/estilo.css">
  <script src="https://sdk.mercadopago.com/js/v2"></script>
</head>
<style>
  /* Força o fundo preto em absolutamente tudo que pode estar causando o cinza */
  html,
  body {
    background-color: #000000 !important;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    /* Evita que a página "dance" para os lados no mobile */
  }

  /* Se você tiver um container principal ou header, garanta que ele também seja preto */
  header,
  .navbar,
  .main-container {
    background-color: #000000 !important;
    border: none !important;
  }

  /* Remove qualquer sombra ou linha que o Bootstrap possa colocar no topo */
  .fixed-top {
    box-shadow: none !important;
  }

  .pagar-page {
    padding-top: 150px;
    min-height: 100vh;
    background-color: #000;
    display: flex;
    justify-content: center;
    align-items: flex-start;
  }

  .pagar-col {
    width: 100%;
    max-width: 500px;
  }

  .pagar-card {
    background-color: #0d0d0d;
    border: 1px solid #333;
    border-radius: 12px;
    width: 100%;
    box-sizing: border-box;
  }

  #cardPaymentBrick_container {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
  }

  #cardPaymentBrick_container,
  #cardPaymentBrick_container * {
    box-sizing: border-box;
  }

  #cardPaymentBrick_container iframe {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
  }

  @media (max-width: 768px) {
    .pagar-page {
      padding-top: 105px !important;
      min-height: calc(100vh - 20px);
    }

    .pagar-page .container {
      padding-left: 12px !important;
      padding-right: 12px !important;
    }

    .pagar-col {
      max-width: 100% !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    .pagar-card {
      padding: 14px !important;
      border-radius: 10px;
    }

    .pagar-title {
      font-size: 1.55rem;
      letter-spacing: 1px !important;
    }

    .pagar-total {
      font-size: 1.8rem !important;
    }

    #cardPaymentBrick_container .mp-bricks-container,
    #cardPaymentBrick_container .mp-checkout-container,
    #cardPaymentBrick_container .mp-checkout-card-form,
    #cardPaymentBrick_container form {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
    }
  }

  @media (max-width: 480px) {
    .pagar-page .container {
      padding-left: 8px !important;
      padding-right: 8px !important;
    }

    .pagar-card {
      padding: 12px !important;
    }
  }
</style>

<body class="bg-black text-white" style="background-color: #000;">

  <main class="main-content pagar-page">
    <div class="container">
      <div class="row justify-content-center mx-0">

        <div class="col-12 col-sm-11 pagar-col">

          <div class="card p-3 p-md-4 shadow-lg pagar-card">

            <div class="text-center mb-4">
              <h2 class="fw-bold text-uppercase pagar-title" style="color: #cd221f; letter-spacing: 2px;">Pagamento</h2>
              <div style="width: 50px; height: 2px; background-color: #cd221f; margin: 10px auto;"></div>
              <p class="small" style="color: #e4e4e4;">Finalize seu pedido com total segurança</p>
              <?php if ($modo_pagamento !== 'production'): ?>
                <p class="small mb-0" style="color: #47d16a;">Modo teste Mercado Pago (sandbox)</p>
              <?php endif; ?>
            </div>

            <div class="mb-4 p-3 text-center" style="background-color: #161616; border-radius: 8px; border: 1px solid #333;">
              <span class="d-block small text-uppercase mb-1" style="color: #888;">Valor Total do Pedido</span>
              <span class="fs-3 fw-bold pagar-total" style="color: #00ff00;">R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></span>

              <?php if (isset($_SESSION['entrega_temporaria'])): ?>
                <div class="mt-2 pt-2 border-top border-secondary">
                  <p class="mb-0" style="color: #e4e4e4; font-size: 0.8rem;">
                    <i class="bi bi-geo-alt-fill me-1" style="color: #cd221f;"></i>
                    Enviando para o endereço escolhido no carrinho.
                  </p>
                </div>
              <?php endif; ?>
            </div>

            <div id="cardPaymentBrick_container">
              <div class="text-center py-4">
                <div class="spinner-border text-danger" role="status">
                  <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2 small" style="color: #e4e4e4; opacity: 0.8;">
                  Carregando formulário seguro...
                </p>
              </div>
            </div>

            <div class="text-center mt-3">
              <p style="font-size: 0.7rem; color: #666;">
                <i class="bi bi-shield-lock-fill me-1"></i> Seus dados são processados de forma criptografada.
              </p>
            </div>

          </div>
        </div>

      </div>
    </div>
  </main>

  <script>
    const publicKeyMercadoPago = <?php echo json_encode($public_key_mercadopago, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const totalParaPagar = <?php echo json_encode((float)$valor_total); ?>;
    const emailCliente = <?php echo json_encode($email_cliente, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    // Inicializa o Mercado Pago
    const mp = new MercadoPago(publicKeyMercadoPago);
  </script>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/mercadopago.js"></script>

</body>

</html>