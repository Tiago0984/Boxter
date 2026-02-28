<section class="banner-home d-flex align-items-start justify-content-center">
    <div class="banner-bg"></div>

    <div class="container container-conteudo">
        <div class="row justify-content-center">
            <div class="col-11 col-md-7 col-lg-6">
                <div class="search-box-container animate__animated animate__fadeInUp">
                    <?php $busca_atual = isset($_GET['busca']) ? htmlspecialchars(trim($_GET['busca']), ENT_QUOTES, 'UTF-8') : ''; ?>
                    <form id="searchProdutosForm" action="index.php#produtos-destaque" method="GET" class="d-flex align-items-center rounded-pill overflow-hidden shadow-lg">
                        <div class="ps-4">
                            <i class="bi bi-search text-muted fs-5"></i>
                        </div>
                        <input type="search" id="searchProdutosInput" name="busca" class="form-control border-0 shadow-none py-3 px-3"
                            placeholder="Buscar produtos..."
                            value="<?php echo $busca_atual; ?>"
                            style="background: transparent; color: #000;">
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
    (function() {
        var form = document.getElementById('searchProdutosForm');
        var input = document.getElementById('searchProdutosInput');
        if (!form || !input) return;

        function limparFiltroBusca() {
            window.location.href = 'index.php#produtos-destaque';
        }

        form.addEventListener('submit', function(e) {
            if (input.value.trim() === '') {
                e.preventDefault();
                limparFiltroBusca();
            }
        });

        input.addEventListener('search', function() {
            if (input.value.trim() === '') {
                limparFiltroBusca();
            }
        });
    })();
</script>
