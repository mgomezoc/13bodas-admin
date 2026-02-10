<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar evento | 13Bodas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 720px;">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 text-center">Activa tu evento</h1>
                <p class="text-center text-muted mb-4"><?= esc((string) $event['couple_title']) ?></p>

                <div class="display-5 text-center fw-bold mb-1">$<?= esc(number_format((float) $price, 2)) ?></div>
                <div class="text-center text-muted mb-4">MXN pago único</div>

                <ul class="mb-4">
                    <li>Quita watermarks demo.</li>
                    <li>Galería y RSVP sin límites demo.</li>
                    <li>Desbloquea mesa de regalos.</li>
                </ul>

                <button id="checkout-button" class="btn btn-primary btn-lg w-100" data-event-id="<?= esc((string) $event['id']) ?>">
                    Pagar con Stripe
                </button>
            </div>
        </div>
    </main>

    <script src="<?= base_url('assets/js/stripe-checkout.js') ?>"></script>
    <script>
        window.checkoutConfig = {
            createSessionUrl: '<?= site_url(route_to('checkout.create_session', $event['id'])) ?>',
            publicKey: '<?= esc((string) env('STRIPE_PUBLISHABLE_KEY', '')) ?>',
        };
    </script>
</body>
</html>
