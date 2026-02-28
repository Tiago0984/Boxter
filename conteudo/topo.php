<?php
// 1. LOGICA DO CONTADOR
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$total_itens = 0;
if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
    $total_itens = array_sum($_SESSION['carrinho']);
}
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-black shadow-sm sticky-top">
        <div class="container d-flex justify-content-between align-items-center">

            <a class="navbar-brand p-0" href="index.php">
                <img src="img/LOGO BOXTER 3.svg" alt="Boxter Auto Peças" class="img-logo-topo">
            </a>

            <div class="collapse navbar-collapse" id="menuBoxter">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 text-uppercase fw-bold align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php#categorias">Categorias</a></li>
                    <li class="nav-item"><a class="nav-link" href="sobre.php">Sobre</a></li>
                    <li class="nav-item"><a class="nav-link" href="servicos.php">Serviços</a></li>
                    <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-painel ms-lg-3 px-3" href="admin/index.php">
                            ÁREA ADMIN
                        </a>
                    </li>
                </ul>
            </div>

            <div class="d-flex align-items-center gap-2 gap-md-3">

                <div class="nav-item dropdown">
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <a class="nav-link nav-icon-ajuste dropdown-toggle d-flex align-items-center p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
                            <i class="bi bi-person-circle fs-4"></i>
                            <span class="d-none d-lg-inline fw-bold ms-1" style="font-size: 0.8rem;">
                                Olá, <?php echo explode(' ', $_SESSION['cliente_nome'])[0]; ?>
                            </span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="min-width: 180px; background-color: #ffffff;">
                            <li>
                                <a class="dropdown-item py-2" href="meus_pedidos.php">
                                    <i class="bi bi-box-seam me-2"></i> Meus Pedidos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="meus_dados.php">
                                    <i class="bi bi-person-vcard me-2"></i> Meus Dados
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item py-2 text-danger fw-bold" href="sair.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> Sair
                                </a>
                            </li>
                        </ul>
                    <?php else: ?>
                        <a href="login_cliente.php" class="nav-link nav-icon-ajuste p-0">
                            <i class="bi bi-person fs-4" style="color: #ffffff;"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <a href="carrinho.php" class="nav-link nav-icon-ajuste position-relative d-inline-block p-0">
                    <i class="bi bi-cart3 fs-4" style="color: #ffffff;"></i>
                    <?php if ($total_itens > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                            style="background-color: #cd221f; font-size: 0.55rem; border: 1px solid #000; padding: 0.25em 0.4em;">
                            <?php echo $total_itens; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <button class="navbar-toggler p-1" type="button" data-bs-toggle="collapse" data-bs-target="#menuBoxter" aria-controls="menuBoxter" aria-expanded="false" style="border: none;">
                    <span class="navbar-toggler-icon" style="width: 1.2em; height: 1.2em;"></span>
                </button>
            </div>

        </div>
    </nav>
</header>

<style>/* Cor normal do botão */
.btn-painel {
    color: #e4e4e4 !important;
    border-color: #cd221f !important; /* Mantendo a borda vermelha da sua marca */
    transition: 0.3s;
}

/* Efeito Hover: muda a cor do texto para o azul solicitado */
.btn-painel:hover {
    color: #0056b3 !important; 
    background-color: transparent !important; /* Mantém o fundo transparente */
    border-color: #0056b3 !important; /* Opcional: muda a borda para azul também */
}</style>