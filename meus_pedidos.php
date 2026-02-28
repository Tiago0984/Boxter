<?php
session_start();
include_once "conexao.php";

// Seguranca: Se o cliente nao estiver logado, redireciona para o login
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login_cliente.php");
    exit;
}

$id_cliente = $_SESSION['cliente_id'];

// Busca os pedidos desse cliente especifico
$sql = "SELECT * FROM tbl_pedido WHERE id_cliente = '$id_cliente' ORDER BY data_pedido DESC";
$res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boxter Auto Pecas - Especialista em Recuperacao e Reparo</title>

    <meta name="description"
        content="Especialistas em reparacao de pecas automotivas com precisao de fabrica. Atendemos todas as marcas na Av. Marechal Tito, 1500.">
    <meta name="keywords"
        content="oficina mecanica, reparo de pecas, auto pecas, recuperacao de componentes, Boxter Sao Miguel">
    <meta name="author" content="Equipe Codera Tech">

    <meta property="og:title" content="Boxter Auto Pecas - Especialista em Recuperacao e Reparo">
    <meta property="og:description"
        content="Especialistas em reparacao de pecas automotivas com precisao de fabrica. Atendemos todas as marcas na Av. Marechal Tito, 1500.">
    <meta property="image" content="https://">
    <meta property="og:type" content="website">

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

<body>

    <?php require_once("conteudo/topo.php"); ?>

    <main class="bg-black text-white meus-pedidos-page" style="padding-top: 130px; min-height: 100vh;">
        <div class="container my-5">
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-box-seam me-3" style="font-size: 2rem; color: #cd221f;"></i>
                <h2 class="text-uppercase fw-bold m-0 text-danger" style="letter-spacing: 1px;">
                    Meus Pedidos
                </h2>
            </div>

            <?php if (mysqli_num_rows($res) == 0): ?>
                <div class="card" style="background-color: #0d0d0d; border: 1px solid #e4e4e4; border-radius: 12px;">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-cart-x mb-3 text-secondary" style="font-size: 3rem; display: block;"></i>
                        <p class="text-secondary fs-5">
                            Ola, <?php echo $_SESSION['cliente_nome']; ?>. Voce ainda nao realizou compras.
                        </p>
                        <a href="index.php" class="btn btn-danger px-5 py-2 fw-bold mt-3 text-uppercase">
                            Ir para a Loja
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive meus-pedidos-wrapper" style="border: 1px solid #e4e4e4; border-radius: 12px; overflow: hidden;">
                    <table class="table table-dark table-hover align-middle m-0 pedidos-table" style="table-layout: fixed; width: 100%;">
                        <colgroup>
                            <col style="width: 18%;">
                            <col style="width: 18%;">
                            <col style="width: 20%;">
                            <col style="width: 18%;">
                            <col style="width: 26%;">
                        </colgroup>
                        <thead style="background-color: #1a1a1a; color: #b0b0b0;">
                            <tr class="border-bottom border-secondary">
                                <th class="py-3 ps-4 text-start">Nº PEDIDO</th>
                                <th class="py-3 text-start">DATA</th>
                                <th class="py-3 text-start">TOTAL</th>
                                <th class="py-3 text-center">STATUS</th>
                                <th class="py-3 text-end pe-4">ACOES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ped = mysqli_fetch_assoc($res)): ?>
                                <tr style="border-bottom: 1px solid #222;">
                                    <td class="fw-bold ps-4 text-start" data-label="Nº Pedido">
                                        #<?php echo str_pad($ped['id_pedido'], 5, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td class="text-start" data-label="Data">
                                        <?php echo date('d/m/Y', strtotime($ped['data_pedido'])); ?>
                                    </td>
                                    <td class="fw-bold text-start" style="color: #00ff00;" data-label="Total">
                                        R$ <?php echo number_format($ped['valor_total_pedido'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="text-center" data-label="Status">
                                        <?php
                                        $status = strtoupper($ped['status_pedido']);
                                        $badgeClass = ($status == 'APPROVED' || $status == 'APROVADO') ? 'bg-success' : 'bg-warning text-dark';
                                        $statusLabel = ($status == 'APPROVED' || $status == 'APROVADO') ? 'Aprovado' : 'Pendente';
                                        ?>
                                        <span class="badge rounded-pill <?php echo $badgeClass; ?> px-3 py-2">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4" data-label="Ações">
                                        <a href="detalhes_pedido.php?id=<?php echo $ped['id_pedido']; ?>"
                                            class="btn btn-sm btn-outline-light px-3">
                                            <i class="bi bi-eye me-1"></i> Detalhes
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once("conteudo/rodape.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="js/slick.js"></script>
    <script src="js/lity.min.js"></script>
    <script src="js/animacao.js"></script>

</body>

</html>
