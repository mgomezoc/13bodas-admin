<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago | 13Bodas</title>
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
            <?php if (($isPaid ?? false) === true): ?>
                <span class="checkout-kicker">Pago confirmado</span>
                <h1 class="h2 fw-bold mt-2">¡Pago exitoso!</h1>
                <p class="text-muted mb-3">Tu evento está en proceso de activación.</p>
                <p class="mb-1"><strong><?= esc((string) ($currency ?? 'MXN')) ?> $<?= esc(number_format((float) ($amount ?? 0.0), 2)) ?></strong></p>
                <p class="text-muted small mb-4">Sesión Stripe: <?= esc((string) ($sessionId ?? '')) ?></p>
            <?php else: ?>
                <span class="checkout-kicker">Verificación pendiente</span>
                <h1 class="h2 fw-bold mt-2">Estamos validando tu pago</h1>
                <p class="text-muted mb-2"><?= esc((string) ($errorMessage ?? 'Stripe aún no confirma el pago.')) ?></p>
                <p class="text-muted small mb-4">Estado actual: <?= esc((string) ($paymentStatus ?? 'unknown')) ?></p>
            <?php endif; ?>

            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="<?= site_url(route_to('admin.events.index')) ?>" class="btn btn-primary">Ir a mis eventos</a>
                <a href="<?= current_url() . '?session_id=' . urlencode((string) ($sessionId ?? '')) ?>" class="btn btn-outline-primary">Reintentar validación</a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
