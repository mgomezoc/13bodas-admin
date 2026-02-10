<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Solicitud de Dominio</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #0a0510 0%, #1a1520 100%); color: #d4a574; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .info-box { background: #f8f9fa; border-left: 4px solid #d4a574; padding: 15px; margin: 20px 0; }
        .btn { display: inline-block; background: #d4a574; color: #fff !important; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🌐 Nueva Solicitud de Dominio</h1>
    </div>
    <div class="content">
        <p>Hola equipo,</p>
        <p>Se recibió una nueva solicitud de dominio personalizado.</p>

        <div class="info-box">
            <p><strong>Evento:</strong> <?= esc((string) $couple_title) ?></p>
            <p><strong>Slug:</strong> <?= esc((string) $slug) ?></p>
            <p><strong>Fecha del Evento:</strong> <?= esc((string) $event_date) ?></p>
            <p><strong>Dominio Solicitado:</strong> <code><?= esc((string) $domain_requested) ?></code></p>
            <p><strong>Solicitado por:</strong> <?= esc((string) $requested_by) ?></p>
            <p><strong>Costo:</strong> <?= esc((string) $price) ?></p>
        </div>

        <p><strong>Siguiente paso:</strong> Configurar DNS y migrar manualmente la invitación.</p>
        <p style="text-align: center;">
            <a href="<?= esc((string) $link) ?>" class="btn">Ver detalles en el panel</a>
        </p>
    </div>
</div>
</body>
</html>
