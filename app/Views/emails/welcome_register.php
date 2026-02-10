<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bienvenido a 13Bodas</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Inter,Arial,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="max-width:620px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                <tr>
                    <td style="background:linear-gradient(135deg,#111827,#1f2937);padding:24px;">
                        <img src="<?= esc(base_url('img/13bodas-logo-blanco-transparente.png')) ?>" alt="13Bodas" style="max-width:130px;height:auto;display:block;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:26px;">
                        <h1 style="margin:0 0 14px;font-size:24px;color:#111827;">¡Hola <?= esc($name !== '' ? $name : '') ?>, bienvenido(a) a 13Bodas! 🎉</h1>
                        <p style="margin:0 0 12px;line-height:1.6;">Tu cuenta se creó correctamente y ya tienes un <strong>evento en modo DEMO</strong> para comenzar a personalizar tu invitación.</p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:16px 0 20px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;">
                            <tr>
                                <td style="padding:14px 16px;">
                                    <p style="margin:0 0 6px;"><strong>Evento:</strong> <?= esc($eventTitle !== '' ? $eventTitle : 'Tu evento') ?></p>
                                    <p style="margin:0;"><strong>Fecha:</strong> <?= esc($eventDate !== '' ? $eventDate : 'Por definir') ?></p>
                                </td>
                            </tr>
                        </table>

                        <h2 style="margin:0 0 10px;font-size:18px;color:#111827;">Siguientes pasos recomendados</h2>
                        <ol style="margin:0 0 20px 18px;padding:0;line-height:1.7;">
                            <li>Entra al panel y <strong>elige tu template</strong>.</li>
                            <li>Completa información del evento, galería y RSVP.</li>
                            <li>Activa tu evento con pago único para quitar modo DEMO.</li>
                        </ol>

                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 16px;">
                            <tr>
                                <td style="padding-right:8px;padding-bottom:8px;">
                                    <a href="<?= esc($eventEditUrl) ?>" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:11px 18px;border-radius:8px;font-weight:600;">Ir a mi evento</a>
                                </td>
                                <td style="padding-right:8px;padding-bottom:8px;">
                                    <a href="<?= esc($dashboardUrl) ?>" style="display:inline-block;background:#111827;color:#fff;text-decoration:none;padding:11px 18px;border-radius:8px;font-weight:600;">Abrir dashboard</a>
                                </td>
                                <?php if (!empty($checkoutUrl)): ?>
                                <td style="padding-bottom:8px;">
                                    <a href="<?= esc($checkoutUrl) ?>" style="display:inline-block;background:#c89d67;color:#111827;text-decoration:none;padding:11px 18px;border-radius:8px;font-weight:600;">Activar evento</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                        </table>

                        <p style="margin:0;line-height:1.6;">Si necesitas apoyo, escríbenos por WhatsApp: <a href="<?= esc($supportWhatsappUrl) ?>" target="_blank" rel="noopener">Equipo 13Bodas</a>.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
