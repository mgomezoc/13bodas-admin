<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Lead - 13Bodas</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 8px 25px rgba(15,23,42,.08);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#111827,#1f2937);padding:20px 24px;color:#ffffff;">
                            <h1 style="margin:0;font-size:22px;line-height:1.3;">Nuevo lead desde la web pública</h1>
                            <p style="margin:8px 0 0 0;font-size:14px;opacity:.9;">13Bodas • Solicitud comercial</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 14px 0;font-size:15px;">Se recibió una nueva solicitud con los siguientes datos:</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;border-bottom:1px solid #e5e7eb;width:35%;font-weight:700;">Nombre</td>
                                    <td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;"><?= esc((string) ($leadData['full_name'] ?? '')) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:700;">Email</td>
                                    <td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;"><?= esc((string) ($leadData['email'] ?? '')) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:700;">WhatsApp</td>
                                    <td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;"><?= esc((string) ($leadData['phone'] ?? '')) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:700;">Tipo de evento</td>
                                    <td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;"><?= esc((string) ($leadData['event_type'] ?? 'No especificado')) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:700;">Fecha estimada</td>
                                    <td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;"><?= esc((string) ($leadData['event_date'] ?? 'No especificada')) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:700;">Paquete</td>
                                    <td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;"><?= esc((string) ($leadData['package_interest'] ?? 'No especificado')) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:700;">Origen</td>
                                    <td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;"><?= esc((string) ($leadData['source'] ?? 'website')) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;font-weight:700;vertical-align:top;">Mensaje</td>
                                    <td style="padding:10px 12px;white-space:pre-line;"><?= esc((string) ($leadData['message'] ?? 'Sin mensaje')) ?></td>
                                </tr>
                            </table>

                            <p style="margin:16px 0 0 0;font-size:13px;color:#6b7280;">Recibido: <?= esc((string) $createdAt) ?></p>

                            <p style="margin:18px 0 0 0;">
                                <a href="<?= esc((string) $dashboardLeadsUrl) ?>" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-size:14px;">Ver leads en panel</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
