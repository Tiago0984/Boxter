<section class="secao-servicos py-5" style="background-color: #e9e9e9;">
    <div class="container">
        <div class="row mb-5 text-center">
            <div class="col-12">
                <h2 class="text-danger fw-bold text-uppercase">Serviços Oferecidos</h2>
                <p class="text-dark mx-auto mt-3" style="max-width: 800px;">
                    A oficina conta com uma variedade de serviços, reparação e diagnóstico para veículos nacionais e importados.
                </p>
            </div>
        </div>

        <div class="row g-4">
            <?php
            // Lista completa de serviços conforme seu layout
            $meus_servicos = [
                ['img' => 'oleo-de-carro2.png', 'nome' => 'Troca de Óleo e Filtros'],
                ['img' => 'veiculo2.png', 'nome' => 'Revisão Completa'],
                ['img' => 'suspensao.png', 'nome' => 'Freios e Suspensão'],
                ['img' => 'diagnostico-de-carro2.png', 'nome' => 'Diagnóstico Eletrônico'],
                ['img' => 'configuracao2.png', 'nome' => 'Reparação de Peças'],
                ['img' => 'venda.png', 'nome' => 'Venda de Peças'],
                ['img' => 'ar-condicionado2.png', 'nome' => 'Ar-Condicionado'],
                ['img' => 'balanceadora-de-rodas.png', 'nome' => 'Alinhamento e Balanceamento']
            ];

            foreach ($meus_servicos as $servico) : ?>
                <div class="col-sm-6 col-md-3">
                    <div class="card-servico p-4 d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="mb-3 container-img-servico">
                            <img src="img/<?php echo $servico['img']; ?>"
                                alt="<?php echo $servico['nome']; ?>"
                                class="img-fluid img-servico-custom">
                        </div>
                        <h6 class="text-white fw-semibold small text-uppercase"><?php echo $servico['nome']; ?></h6>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>