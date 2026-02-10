<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Pago exitoso</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
<div class="container" style="max-width:600px;"><div class="card shadow-sm"><div class="card-body p-4 text-center">
<h1 class="h3">¡Pago exitoso!</h1>
<p>Tu evento ya está activado.</p>
<?php if (!empty($sessionId)): ?><p class="text-muted small">Sesión: <?= esc((string) $sessionId) ?></p><?php endif; ?>
<a href="<?= base_url('admin/events') ?>" class="btn btn-primary">Ir a mis eventos</a>
</div></div></div>
</body>
</html>
