<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\Resend;

class InvitationMailer
{
    public function __construct(private Resend $config = new Resend())
    {
    }

    public function sendInvitation(array $event, array $guest, string $inviteUrl): array
    {
        $toEmail = trim((string) ($guest['email'] ?? ''));
        $guestName = trim((string) ($guest['first_name'] ?? '') . ' ' . (string) ($guest['last_name'] ?? ''));

        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'El correo del invitado no es válido.'];
        }

        $from = trim($this->config->fromName . ' <' . $this->config->fromEmail . '>');
        $subject = 'Invitación: ' . ($event['couple_title'] ?? 'Nuestro evento');
        $html = $this->buildHtml($event, $guestName, $inviteUrl);

        $response = $this->sendEmail([
            'from' => $from,
            'to' => [$toEmail],
            'subject' => $subject,
            'html' => $html,
        ]);

        if (!$response['success']) {
            return $response;
        }

        return ['success' => true, 'message' => 'Invitación enviada correctamente.'];
    }

    private function buildHtml(array $event, string $guestName, string $inviteUrl): string
    {
        $eventTitle = $event['couple_title'] ?? 'Nuestro evento';
        $eventDate = $event['event_date_start'] ?? '';
        $eventLocation = $event['venue_name'] ?? ($event['venue_address'] ?? '');

        $lines = [
            '<h2>Te invitamos a nuestra boda</h2>',
            '<p>Hola ' . esc($guestName) . ',</p>',
            '<p>Te compartimos la invitación de <strong>' . esc((string) $eventTitle) . '</strong>.</p>',
        ];

        if ($eventDate !== '') {
            $lines[] = '<p><strong>Fecha:</strong> ' . esc((string) $eventDate) . '</p>';
        }

        if ($eventLocation !== '') {
            $lines[] = '<p><strong>Ubicación:</strong> ' . esc((string) $eventLocation) . '</p>';
        }

        $lines[] = '<p><a href="' . esc($inviteUrl) . '" target="_blank" rel="noopener">Confirmar asistencia</a></p>';
        $lines[] = '<p>— ' . esc((string) ($this->config->fromName)) . '</p>';

        return implode('', $lines);
    }

    private function sendEmail(array $payload): array
    {
        $ch = curl_init(rtrim($this->config->apiUrl, '/') . '/emails');
        if ($ch === false) {
            return ['success' => false, 'message' => 'No se pudo inicializar el envío de correo.'];
        }

        $jsonPayload = json_encode($payload);
        if ($jsonPayload === false) {
            return ['success' => false, 'message' => 'No se pudo preparar el correo.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->config->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $jsonPayload,
        ]);

        $responseBody = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false) {
            return ['success' => false, 'message' => 'Error al enviar correo: ' . $error];
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            return ['success' => false, 'message' => 'El correo no pudo enviarse.'];
        }

        return ['success' => true, 'message' => 'Correo enviado correctamente.'];
    }
}
