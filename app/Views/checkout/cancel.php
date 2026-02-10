<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago cancelado | 13Bodas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/checkout.css') ?>">
</head>
<body class="checkout-page">
<main class="checkout-wrapper container py-4 py-md-5">
    <section class="checkout-card card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5 text-center">
            <span class="checkout-kicker">Pago cancelado</span>
            <h1 class="h2 fw-bold mt-2">No se realizó ningún cargo</h1>
            <p class="text-muted mb-4">Puedes volver a intentar cuando estés listo.</p>

            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="<?= site_url(route_to('admin.events.index')) ?>" class="btn btn-outline-primary">Volver a eventos</a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
