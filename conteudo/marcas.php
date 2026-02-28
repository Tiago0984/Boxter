<section class="secao-marcas">
    <div class="container text-center">
        <h2 class="text-danger fw-bold text-uppercase">Marcas Atendidas</h2>
        <p class="text-white mb-5 opacity-75">Serviço confiável para uma ampla variedade de fabricantes.</p>

        <div class="slider-marcas">
            <?php
            $marcas = [
                ['img' => 'logovolkswagen.png', 'nome' => 'Volkswagen'],
                ['img' => 'logoaudi.png', 'nome' => 'Audi'],
                ['img' => 'logoford.png', 'nome' => 'Ford'],
                ['img' => 'logofiat.png', 'nome' => 'Fiat'],
                ['img' => 'logohonda.png', 'nome' => 'Honda'],
                ['img' => 'logotoyota.png', 'nome' => 'Toyota'],
                ['img' => 'logochevrolet.png', 'nome' => 'Chevrolet']
            ];

            foreach ($marcas as $m) : ?>
                <div class="px-2">
                    <div class="rounded card-marca-logo">
                        <img src="img/<?php echo $m['img']; ?>"
                            alt="<?php echo $m['nome']; ?>"
                            class="img-fluid">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>