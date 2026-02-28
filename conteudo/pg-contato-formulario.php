<section class="contato-formulario py-5"
    style="background: linear-gradient(rgba(248, 249, 250, 0.7), rgba(248, 249, 250, 0.7)), 
           url('img/fundoContato.png') center right / 85% no-repeat fixed;">

    <div class="container py-lg-5">
        <div class="row g-5 align-items-stretch">

            <div class="col-lg-5">
                <div class="form-container bg-white bg-opacity-75 p-4 p-md-5 rounded-4 shadow h-100">

                    <?php if (isset($ok) && $ok == 1): ?>
                        <div class="alert alert-success border-0 shadow-sm text-center fw-bold rounded-pill mb-4 animate__animated animate__fadeIn">
                            ✅ Mensagem enviada com sucesso!
                        </div>
                    <?php elseif (isset($ok) && $ok == 2): ?>
                        <div class="alert alert-danger border-0 shadow-sm text-center fw-bold rounded-pill mb-4 animate__animated animate__shakeX">
                            ❌ Erro ao enviar. Tente novamente.
                        </div>
                    <?php endif; ?>

                    <h2 class="text-danger fw-bold text-center text-uppercase mb-4">Contato</h2>

                    <form action="contato.php" method="POST">
                        <div class="mb-3">
                            <input type="text" name="nome" class="form-control form-control-lg border-dark rounded-pill" placeholder="Nome" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control form-control-lg border-dark rounded-pill" placeholder="Email" required>
                        </div>
                        <div class="mb-3">
                            <input type="tel" name="telefone" class="form-control form-control-lg border-dark rounded-pill" placeholder="Telefone" required>
                        </div>
                        <div class="mb-3">
                            <select name="motivo" class="form-select form-select-lg border-dark rounded-pill text-muted">
                                <option selected disabled>Serviço</option>
                                <option value="Mecanica Geral">Mecânica Geral</option>
                                <option value="Injecao Eletronica">Injeção Eletrônica</option>
                                <option value="Reparacao de Pecas">Reparação de Peças</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <textarea name="mensagem" class="form-control border-dark rounded-4" rows="4" placeholder="Escreva sua mensagem"></textarea>
                        </div>

                        <div class="text-start">
                            <button type="submit" class="btn btn-danger btn-lg px-5 py-2 fw-bold text-uppercase rounded-pill shadow-sm">
                                Enviar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5 offset-lg-2">
                <div class="info-contato d-flex flex-column justify-content-between h-100 py-2">

                    <div class="logo-topo text-center text-lg-start">
                        <img src="img/logoBoxter4.png" alt="Boxter Auto Peças" class="img-fluid" style="max-height: 110px;">
                    </div>

                    <div class="info-base text-center text-lg-start mt-5 mt-lg-0">
                        <div class="fs-5 fw-bold text-dark lh-lg">
                            <p class="mb-1">Telefone: (xx) xxxxx-xxxx</p>
                            <p class="mb-1 d-flex align-items-center justify-content-center justify-content-lg-start gap-2">
                                <a href="https://wa.me/5511958435174" target="_blank" class="text-decoration-none text-dark d-flex align-items-center gap-2">
                                    Whatsapp: (11) 95843-5174
                                    <i class="bi bi-whatsapp text-success fs-3"></i>
                                </a>
                            </p>
                            <p class="mb-0">Endereço: Av. Marechal Tito, 1500</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>