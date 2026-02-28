<?php
include_once __DIR__ . '/../conexao.php';

// Busca os produtos da sua tabela
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$busca_esc = mysqli_real_escape_string($conn, $busca);

if ($busca !== '') {
    $sql = "SELECT * FROM tbl_pecas 
                WHERE nome_peca LIKE '%$busca_esc%' 
                ORDER BY nome_peca ASC 
                LIMIT 24";
} else {
    $sql = "SELECT * FROM tbl_pecas ORDER BY nome_peca ASC LIMIT 12";
}
$res = mysqli_query($conn, $sql);
?>

<section id="produtos-destaque" class="py-5" style="background-color: #f8f9fa;">
    <div class="container text-center">
        <h2 class="text-danger fw-bold text-uppercase">Produtos em Destaque</h2>
        <?php if (!empty($busca)): ?>
            <p class="mb-3">
                Busca ativa: <strong><?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?></strong>
            </p>
        <?php endif; ?>
        <p class="text-muted mb-5">Linha selecionada com o que há de melhor em peças automotivas.</p>

        <div class="row g-4">
            <?php
            if (isset($res) && mysqli_num_rows($res) > 0) :
                while ($p = mysqli_fetch_assoc($res)) : ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm card-produto">
                            <div class="p-3 bg-white text-center">
                                <img src="img/<?php echo $p['img_peca']; ?>"
                                    class="img-fluid"
                                    alt="<?php echo $p['nome_peca']; ?>"
                                    style="height: 180px; object-fit: contain;">
                            </div>

                            <div class="card-body d-flex flex-column bg-white border-top">
                                <h6 class="card-title text-dark mb-2 text-start" style="height: 40px; overflow: hidden;">
                                    <?php echo $p['nome_peca']; ?>
                                </h6>

                                <div class="text-start mt-auto">
                                    <span class="text-muted small">R$</span>
                                    <span class="fs-4 fw-bold text-dark">
                                        <?php echo number_format($p['preco_venda_peca'], 2, ',', '.'); ?>
                                    </span>
                                    <p class="text-success small mb-3">À vista no Pix</p>
                                </div>

                                <a href="carrinho.php?add=<?php echo $p['id_peca']; ?>&retorno=index"
                                    class="btn w-100 fw-bold text-uppercase py-2 mb-2"
                                    style="border: 2px solid #cd221f; color: #cd221f; background: transparent;">
                                    <i class="bi bi-cart-plus me-2"></i> Adicionar
                                </a>

                                <a href="carrinho.php?add=<?php echo $p['id_peca']; ?>"
                                    class="btn w-100 fw-bold text-uppercase py-2 text-white"
                                    style="background-color: #cd221f; border: none;">
                                    <i class="bi bi-bag-check me-2"></i> Comprar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div class="col-12">
                    <p class="text-muted">
                        <?php echo !empty($busca) ? 'Nenhum produto encontrado para essa busca.' : 'Nenhum produto encontrado no banco de dados.'; ?>
                    </p>
                    <?php if (!empty($busca)): ?>
                        <a href="index.php#produtos-destaque" class="btn btn-outline-danger mt-2">Ver todos os produtos</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>