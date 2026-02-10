<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar evento | 13Bodas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/checkout.css') ?>">
</head>
<body class="checkout-page">
    <main class="checkout-wrapper container py-4 py-md-5">
        <section class="checkout-card card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="checkout-header text-center mb-4">
                    <span class="checkout-kicker">Pago seguro</span>
                    <h1 class="h2 fw-bold mb-2">Activa tu evento</h1>
                    <p class="text-muted mb-0"><?= esc((string) $event['couple_title']) ?></p>
                </div>

                <div class="checkout-price text-center mb-4">
                    <p class="checkout-price-amount mb-1">$<?= esc(number_format((float) $price, 2)) ?></p>
                    <p class="text-muted mb-0">MXN · pago único</p>
                </div>

                <div class="checkout-benefits card border-0 mb-4">
                    <div class="card-body p-3 p-md-4">
                        <p class="checkout-benefits-title mb-2">Incluye:</p>
                        <ul class="checkout-benefits-list mb-0">
                            <li>Quita watermarks demo.</li>
                            <li>Galería y RSVP sin límites demo.</li>
                            <li>Desbloquea mesa de regalos.</li>
                        </ul>
                    </div>
                </div>

                <button
                    id="checkout-button"
                    class="btn checkout-btn btn-lg w-100"
                    data-event-id="<?= esc((string) $event['id']) ?>"
                >
                    <span class="checkout-btn-label">Pagar con Stripe</span>
                </button>

                <p id="checkout-feedback" class="checkout-feedback text-center mt-3 mb-0" role="status" aria-live="polite"></p>

                <div class="checkout-trust mt-4 text-center">
                    <span class="checkout-trust-item">🔒 Pago protegido por Stripe</span>
                    <span class="checkout-trust-item">🛡️ Datos cifrados SSL</span>
                </div>
            </div>
        </section>
    </main>

    <script>
        window.checkoutConfig = {
            createSessionUrl: '<?= site_url(route_to('checkout.create_session', $event['id'])) ?>',
            csrfTokenName: '<?= esc(csrf_token()) ?>',
            csrfHash: '<?= esc(csrf_hash()) ?>',
        };
    </script>
    <script src="<?= base_url('assets/js/stripe-checkout.js') ?>"></script>
</body>
</html>
