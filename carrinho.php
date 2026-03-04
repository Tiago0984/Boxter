<?php
session_start();
include_once "conexao.php";

$cep_cliente = $_SESSION['cliente_cep'] ?? '';

// --- INÍCIO DA NOVA LÓGICA DE PESO ---
$peso_total_carrinho = 0;
$subtotal_produtos = 0;

if (isset($_SESSION['carrinho']) && count($_SESSION['carrinho']) > 0) {
    foreach ($_SESSION['carrinho'] as $id => $quantidade) {
        $id = mysqli_real_escape_string($conn, $id);
        $sql = "SELECT preco_venda_peca, peso_peca FROM tbl_pecas WHERE id_peca = '$id'";
        $res = mysqli_query($conn, $sql);
        $dados = mysqli_fetch_assoc($res);

        if ($dados) {
            // Soma o valor total dos produtos
            $subtotal_produtos += ($dados['preco_venda_peca'] * $quantidade);

            // Soma o peso total (Peso da peça * Quantidade)
            // Aqui usamos a coluna no singular 'peso_peca' que você ajustou
            $peso_total_carrinho += ($dados['peso_peca'] * $quantidade);
        }
    }
}
// --- FIM DA LÓGICA DE PESO ---

// Frete será calculado via Frenet (AJAX)
$valor_frete = 0;
$total_geral = $subtotal_produtos; // Será atualizado quando cliente escolher frete

// --- LÓGICA DE ADICIONAR AO CARRINHO (Sua lógica original) ---
if (isset($_GET['add'])) {
    $id = $_GET['add'];
    if (!isset($_SESSION['carrinho'][$id])) {
        $_SESSION['carrinho'][$id] = 1;
    } else {
        $_SESSION['carrinho'][$id] += 1;
    }
    header("Location: " . (isset($_GET['retorno']) && $_GET['retorno'] == 'index' ? "index.php" : "carrinho.php"));
    exit;
}

// 3. LÓGICA DE LIMPAR CARRINHO
if (isset($_GET['limpar']) && $_GET['limpar'] == 'true') {
    unset($_SESSION['carrinho']);
    header("Location: carrinho.php");
    exit;
}
?>

<!-- HTML 5 - Meu site -->
<!DOCTYPE html>
<!-- Sempre mudar para pt-br -->
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boxter Auto Peças - Especialista em Recuperação e Reparo</title>

    <!-- Descrição do site (SEO e compartilhamento) -->
    <meta name="description"
        content="Especialistas em reparação de peças automotivas com precisão de fábrica. Atendemos todas as marcas na Av. Marechal Tito, 1500.">

    <!-- Palavras-chave -->
    <meta name="keywords"
        content="oficina mecânica, reparo de peças, auto peças, recuperação de componentes, Boxter São Miguel">

    <!-- Autor do site -->
    <meta name="author" content="Equipe Codera Tech">

    <!-- Open Graph (Quando tiver um compartilhamento com nosso o Whats, Face, Linkedin) -->
    <meta property="og:title" content="Boxter Auto Peças - Especialista em Recuperação e Reparo">
    <meta property="og:description"
        content="Especialistas em reparação de peças automotivas com precisão de fábrica. Atendemos todas as marcas na Av. Marechal Tito, 1500.">
    <meta property="image" content="https://">
    <meta property="og:type" content="website">

    <!--Icon favicon e app-->
    <link rel="apple-touch-icon" sizes="57x57" href="img/icon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="img/icon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="img/icon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="img/icon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="img/icon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="img/icon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="img/icon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="img/icon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="img/icon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="img/icon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="img/icon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/icon/favicon-16x16.png">
    <link rel="manifest" href="img/icon/manifest.json">
    <meta name="msapplication-TileColor" content="#cd221f">
    <meta name="msapplication-TileImage" content="img/icon/ms-icon-144x144.png">
    <meta name="theme-color" content="#cd221f">

    <link rel="stylesheet" href="css/reset.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <link rel="stylesheet" type="text/css" href="css/slick.css" />
    <link rel="stylesheet" type="text/css" href="css/slick-theme.css" />

    <link rel="stylesheet" href="css/lity.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <link rel="stylesheet" href="css/estilo.css">

</head>
<style>
    /* Cor do texto que o usuário digita e placeholders */
    #campos_entrega input.form-control {
        color: #e4e4e4 !important;
    }

    /* Cor do texto de exemplo (Placeholder) */
    #campos_entrega input.form-control::placeholder {
        color: #e4e4e4 !important;
        opacity: 0.8;
        /* Ajuste para 1 se quiser brilho total */
    }

    /* Suporte para diferentes navegadores */
    #campos_entrega input.form-control::-webkit-input-placeholder {
        color: #e4e4e4 !important;
    }

    #campos_entrega input.form-control::-moz-placeholder {
        color: #e4e4e4 !important;
    }

    #campos_entrega input.form-control:-ms-input-placeholder {
        color: #e4e4e4 !important;
    }

    .carrinho-page {
        background-color: #000;
        min-height: 65vh;
    }

    .carrinho-actions-left {
        display: flex;
        gap: 8px;
    }

    @media (max-width: 768px) {
        .carrinho-page .container {
            padding-top: 96px !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .carrinho-title {
            font-size: 2.1rem !important;
            margin-bottom: 1rem !important;
        }

        .carrinho-table {
            border: 0 !important;
            background: transparent !important;
        }

        .carrinho-table thead {
            display: none;
        }

        .carrinho-table,
        .carrinho-table tbody,
        .carrinho-table tr,
        .carrinho-table td {
            display: block;
            width: 100%;
        }

        .carrinho-table tr {
            border-bottom: 1px solid #2e3640;
            padding: 10px 12px;
            background: linear-gradient(180deg, #171d24 0%, #131920 100%);
        }

        .carrinho-table td {
            border: 0 !important;
            padding: 6px 0 !important;
            text-align: right !important;
            white-space: normal !important;
        }

        .carrinho-table td::before {
            content: attr(data-label);
            float: left;
            color: #9aa5b1;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
        }

        .carrinho-table td[data-label="Peca"] {
            text-align: left !important;
            font-weight: 600;
            color: #fff;
        }

        .carrinho-actions {
            flex-direction: column;
            align-items: stretch !important;
            gap: 12px !important;
        }

        .carrinho-actions-left {
            width: 100%;
        }

        .carrinho-actions-left .btn-outline-light {
            flex: 1;
        }

        .btn-finalizar-carrinho {
            width: 100%;
        }

        .carrinho-login-helper {
            width: 100%;
        }

        .carrinho-login-helper .btn {
            width: 100%;
        }
    }
</style>

<body>

    <!--Inicio da pagina / Cabeçalho-->
    <?php require_once("conteudo/topo.php"); ?>
    <!-- Fim do cabeçalho -->

    <main class="bg-black carrinho-page">
        <div class="container my-5" style="padding-top: 100px;">
            <h2 class="text-uppercase fw-bold mb-4 carrinho-title" style="color: #ff0000;">Meu Carrinho</h2>

            <?php if (empty($_SESSION['carrinho'])): ?>
                <div class="alert text-white text-center py-4" style="background-color: #000; border: 1px solid #e4e4e4; border-radius: 8px;">
                    <i class="bi bi-cart-x mb-2" style="font-size: 2rem; display: block;"></i>
                    Seu carrinho está vazio.
                </div>
                <a href="index.php" class="btn btn-danger">Voltar para Loja</a>
            <?php else: ?>
                <div class="tabela-pecas-container mb-4">
                    <table class="table table-dark table-hover align-middle mb-0 carrinho-table">
                        <thead>
                            <tr>
                                <th>Peça</th>
                                <th>Qtd</th>
                                <th>Valor Unitário</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ids_array = array_keys($_SESSION['carrinho']);
                            $ids_string = implode(',', $ids_array);
                            $sql_itens = "SELECT * FROM tbl_pecas WHERE id_peca IN ($ids_string)";
                            $res_itens = mysqli_query($conn, $sql_itens);

                            while ($item = mysqli_fetch_assoc($res_itens)):
                                $id = $item['id_peca'];
                                $quantidade = $_SESSION['carrinho'][$id];
                                $subtotal_item = $item['preco_venda_peca'] * $quantidade;
                            ?>
                                <tr>
                                    <td data-label="Peca"><?php echo $item['nome_peca']; ?></td>
                                    <td data-label="Qtd"><?php echo $quantidade; ?></td>
                                    <td data-label="Valor Unitario">R$ <?php echo number_format($item['preco_venda_peca'], 2, ',', '.'); ?></td>
                                    <td data-label="Subtotal" class="text-end" style="color: #00ff00;">R$ <?php echo number_format($subtotal_item, 2, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4 d-flex align-items-stretch">
                    <div class="col-md-7 mb-4">
                        <div class="card bg-dark text-white border-secondary p-3 h-100">
                            <h5 class="fw-bold mb-3" style="color: #cd221f;">
                                <i class="bi bi-truck me-2"></i>ENTREGA
                            </h5>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="tipo_entrega" id="entrega_cadastro" checked onchange="toggleEntrega()">
                                <label class="form-check-label small text-white" for="entrega_cadastro">
                                    Entregar no meu endereço de cadastro
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="tipo_entrega" id="entrega_outro" onchange="toggleEntrega()">
                                <label class="form-check-label small text-white" for="entrega_outro">
                                    Entregar em outro endereço
                                </label>
                            </div>

                            <div id="campos_entrega" style="display: none; border-top: 1px solid #333; padding-top: 15px;">
                                <div class="row g-2 mb-2">
                                    <div class="col-8">
                                        <input type="text" id="ent_cep" class="form-control form-control-sm bg-black text-white border-secondary" placeholder="CEP (00000-000)">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" id="ent_uf" class="form-control form-control-sm bg-black text-white border-secondary" placeholder="UF" readonly>
                                    </div>
                                </div>
                                <input type="text" id="ent_rua" class="form-control form-control-sm mb-2 bg-black text-white border-secondary" placeholder="Rua / Logradouro">
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <input type="text" id="ent_num" class="form-control form-control-sm bg-black text-white border-secondary" placeholder="Nº">
                                    </div>
                                    <div class="col-8">
                                        <input type="text" id="ent_comp" class="form-control form-control-sm bg-black text-white border-secondary" placeholder="Apto, Bloco, etc.">
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" id="ent_bairro" class="form-control form-control-sm bg-black text-white border-secondary" placeholder="Bairro">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" id="ent_cidade" class="form-control form-control-sm bg-black text-white border-secondary" placeholder="Cidade">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 mb-4">
                        <div class="card bg-dark text-white border-secondary p-3 h-100">
                            <h5 class="fw-bold mb-3" style="color: #cd221f;">
                                <i class="bi bi-receipt me-2"></i>RESUMO
                            </h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal Peças:</span>
                                <span>R$ <?php echo number_format($subtotal_produtos, 2, ',', '.'); ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2 text-info">
                                <span>Frete:</span>
                                <span id="frete_valor">A calcular</span>
                            </div>

                            <!-- Opções de frete (Frenet) -->
                            <div id="opcoes_frete_container" style="display: none; border-top: 1px solid #444; padding-top: 12px; margin-top: 12px;">
                                <small class="text-secondary d-block mb-2">Escolha a transportadora:</small>
                                <div id="opcoes_frete_lista"></div>
                            </div>

                            <hr class="border-secondary mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h4 fw-bold mb-0">TOTAL:</span>
                                <span class="h4 fw-bold mb-0" style="color: #00ff00;" id="total_valor">R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between mt-4 gap-3 align-items-center carrinho-actions">
                    <div class="carrinho-actions-left">
                        <a href="carrinho.php?limpar=true" class="btn fw-bold py-2 px-3"
                            style="background-color: #cd221f; color: #e4e4e4; border: none;" title="Limpar Carrinho">
                            <i class="bi bi-trash3"></i>
                        </a>
                        <a href="index.php" class="btn btn-outline-light fw-bold py-2 px-4">
                            <i class="bi bi-arrow-left me-2"></i>CONTINUAR COMPRANDO
                        </a>
                    </div>

                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <button onclick="irParaPagamento()" class="btn btn-success btn-lg fw-bold px-5 shadow btn-finalizar-carrinho">
                            FINALIZAR COMPRA <i class="bi bi-chevron-right ms-2"></i>
                        </button>
                    <?php else: ?>
                        <div class="d-flex flex-column align-items-center text-center carrinho-login-helper">
                            <small class="text-secondary mb-2">Faça login para finalizar o pedido.</small>
                            <a href="login_cliente.php" class="btn btn-danger btn-lg fw-bold px-5 shadow" style="background-color: #cd221f; border: none;">
                                ENTRAR PARA FINALIZAR <i class="bi bi-person-fill ms-2"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- RODAPE / FOOTER -->
    <?php require_once("conteudo/rodape.php") ?>
    <!-- FIM RODAPE / FOOTER -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script type="text/javascript" src="js/slick.js"></script>

    <script src="js/lity.min.js"></script>

    <script src="js/animacao.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        function toggleEntrega() {
            const check = document.getElementById('entrega_cadastro');
            const campos = document.getElementById('campos_entrega');
            campos.style.display = check.checked ? 'none' : 'block';

            // Se selecionar "entregar no endereço de cadastro", calcula frete também
            if (check.checked) {
                const cep_cadastro = '<?php echo $cep_cliente; ?>';
                if (cep_cadastro && cep_cadastro.length > 0) {
                    calcularFreteViaFrenet(cep_cadastro.replace(/\D/g, ''));
                } else {
                    document.getElementById('frete_valor').innerHTML = 'Cadastre um CEP primeiro';
                }
            }
        }

        $(document).ready(function() {
            $('#ent_cep').on('blur', function() {
                var cep = $(this).val().replace(/\D/g, '');
                if (cep.length === 8) {
                    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/", function(dados) {
                        if (!("erro" in dados)) {
                            $("#ent_rua").val(dados.logradouro);
                            $("#ent_bairro").val(dados.bairro);
                            $("#ent_cidade").val(dados.localidade);
                            $("#ent_uf").val(dados.uf);
                            $("#ent_num").focus();

                            // 🆕 CALCULA FRETE VIA FRENET
                            calcularFreteViaFrenet(cep);
                        }
                    });
                }
            });
        });

        function irParaPagamento() {
            const usarCadastro = document.getElementById('entrega_cadastro').checked;

            let dadosEntrega = {
                usar_cadastro: usarCadastro
            };

            // --- NOVA VALIDAÇÃO DE SEGURANÇA ---
            if (!usarCadastro) {
                const cep = document.getElementById('ent_cep').value.trim();
                const rua = document.getElementById('ent_rua').value.trim();
                const num = document.getElementById('ent_num').value.trim();

                if (cep === "" || rua === "" || num === "") {
                    alert("⚠️ Atenção: Para entregar em um novo endereço, preencha o CEP, a Rua e o Número!");

                    // Dá foco no campo que estiver vazio para ajudar o usuário
                    if (cep === "") document.getElementById('ent_cep').focus();
                    else if (rua === "") document.getElementById('ent_rua').focus();
                    else if (num === "") document.getElementById('ent_num').focus();

                    return; // 🛑 PARA AQUI e não envia para o pagar.php
                }

                // Se chegou aqui, os campos obrigatórios estão ok, preenchemos o objeto
                dadosEntrega.cep = cep;
                dadosEntrega.logradouro = rua;
                dadosEntrega.numero = num;
                dadosEntrega.complemento = document.getElementById('ent_comp').value;
                dadosEntrega.bairro = document.getElementById('ent_bairro').value;
                dadosEntrega.cidade = document.getElementById('ent_cidade').value;
                dadosEntrega.uf = document.getElementById('ent_uf').value;
            }

            // Garante frete selecionado antes de seguir
            if (!window.frete_selecionado || !window.frete_selecionado.valor) {
                alert("Atencao: selecione uma opcao de frete para continuar.");
                return;
            }

            // Adiciona dados do frete selecionado
            if (window.frete_selecionado) {
                dadosEntrega.frete_transportadora = window.frete_selecionado.transportadora;
                dadosEntrega.frete_valor = window.frete_selecionado.valor;
                dadosEntrega.frete_prazo = window.frete_selecionado.prazo;
            }

            // Se usarCadastro for true OU se passou na validação acima, envia os dados
            fetch('atualizar_sessao_entrega.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dadosEntrega)
                })
                .then(() => {
                    window.location.href = 'pagar.php';
                });
        }

        // 🆕 CALCULA FRETE VIA FRENET
        // Esta função deve estar disponível globalmente
        function calcularFreteViaFrenet(cep) {
            const peso = <?php echo $peso_total_carrinho; ?>;
            const valor_produtos = <?php echo $subtotal_produtos; ?>;

            // Validação básica
            if (!cep || cep.length !== 8) {
                console.error('CEP inválido');
                document.getElementById('frete_valor').innerHTML = 'CEP inválido';
                return;
            }

            console.log('Calculando frete para CEP:', cep);
            document.getElementById('frete_valor').innerHTML = 'Calculando...';

            fetch('calcular_frete_frenet.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cep: cep,
                        peso: peso,
                        valor_produtos: valor_produtos
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Resposta da API:', data);
                    if (data && data.opcoes && data.opcoes.length > 0) {
                        exibirOpcoesFrete(data.opcoes);
                    } else {
                        console.error('Erro ao calcular frete:', data.message);
                        aplicarFreteFallback();
                    }
                })
                .catch(err => {
                    console.error('Erro AJAX:', err);
                    aplicarFreteFallback();
                });
        }

        function calcularFreteFallback(peso) {
            const frete_base = 15.00;
            const valor_por_kg = 3.50;

            let valor_calculado = frete_base + (peso * valor_por_kg);
            valor_calculado = Math.max(12.90, valor_calculado);
            valor_calculado = Math.min(89.90, valor_calculado);

            valor_calculado = Math.ceil(valor_calculado * 10) / 10;
            valor_calculado = Math.floor(valor_calculado) + 0.90;

            return valor_calculado;
        }

        function aplicarFreteFallback() {
            const peso = <?php echo $peso_total_carrinho; ?>;
            const subtotal = <?php echo $subtotal_produtos; ?>;
            const freteFallback = calcularFreteFallback(peso);
            const prazoFallback = freteFallback > 50 ? 3 : 5;

            document.getElementById('frete_valor').innerHTML = 'R$ ' + freteFallback.toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });
            document.getElementById('total_valor').innerHTML = 'R$ ' + (subtotal + freteFallback).toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });

            // Salva na sessao via fetch quando finalizar
            window.frete_selecionado = {
                transportadora: 'Frete Automatico',
                valor: freteFallback,
                prazo: prazoFallback
            };
        }

        function exibirOpcoesFrete(opcoes) {
            const container = document.getElementById('opcoes_frete_container');
            const lista = document.getElementById('opcoes_frete_lista');

            lista.innerHTML = '';

            if (!opcoes || opcoes.length === 0) {
                lista.innerHTML = '<small class="text-danger">Nenhuma transportadora disponivel</small>';
                container.style.display = 'block';
                return;
            }

            opcoes.forEach((opcao, index) => {
                const html = `
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="frete_selecionado" 
                               id="frete_${index}" value="${opcao.valor}" 
                               ${index === 0 ? 'checked' : ''} 
                               onchange="atualizarValorFrete('${opcao.transportadora}', ${opcao.valor}, ${opcao.prazo})">
                        <label class="form-check-label small text-white" for="frete_${index}">
                            <strong>${opcao.transportadora}</strong> - ${opcao.valor_formatado} 
                            <span class="text-secondary">(${opcao.prazo} dias)</span>
                        </label>
                    </div>
                `;
                lista.innerHTML += html;
            });

            container.style.display = 'block';

            // Atualiza o valor do primeiro frete automaticamente
            if (opcoes[0]) {
                atualizarValorFrete(opcoes[0].transportadora, opcoes[0].valor, opcoes[0].prazo);
            }
        }

        function atualizarValorFrete(transportadora, valor, prazo) {
            const valorNum = parseFloat(valor) || 0;
            const subtotal = <?php echo $subtotal_produtos; ?>;
            const total = subtotal + valorNum;

            // Atualiza o valor do frete
            document.getElementById('frete_valor').innerHTML = 'R$ ' + valorNum.toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });

            // Atualiza o total
            document.getElementById('total_valor').innerHTML = 'R$ ' + total.toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });

            // Armazena o frete selecionado na sessao (sera usado no pagar.php)
            window.frete_selecionado = {
                transportadora: transportadora,
                valor: valorNum,
                prazo: prazo
            };
        }

$(document).ready(function() {

            // Se já estiver selecionado "endereço de cadastro"
            if ($('#entrega_cadastro').is(':checked')) {

                const cep_cadastro = '<?php echo $cep_cliente; ?>';

                if (cep_cadastro && cep_cadastro.length > 0) {
                    calcularFreteViaFrenet(cep_cadastro.replace(/\D/g, ''));
                } else {
                    $('#frete_valor').html('Cadastre um CEP primeiro');
                }
            }

        });
    </script>
</body>

</html>
