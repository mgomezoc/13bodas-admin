<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\Resend;

class RsvpMailer
{
    public function __construct(private Resend $config = new Resend())
    {
    }

    public function sendConfirmation(array $event, array $payload): array
    {
        $toEmail = $payload['email'] ?? '';
        $toName = $payload['name'] ?? '';

        if ($toEmail === '') {
            return ['success' => false, 'message' => 'El correo del invitado es obligatorio.'];
        }

        $from = trim($this->config->fromName . ' <' . $this->config->fromEmail . '>');
        $subject = 'Confirmación RSVP - ' . ($event['couple_title'] ?? 'Evento');
        $statusLabel = $this->formatStatus((string) ($payload['attending'] ?? ''));

        $html = $this->buildHtml($event, $payload, $statusLabel);

        $response = $this->sendEmail([
            'from' => $from,
            'to' => [$toEmail],
            'subject' => $subject,
            'html' => $html,
        ]);

        if (!$response['success']) {
            return $response;
        }

        return ['success' => true, 'message' => 'Confirmación enviada al correo proporcionado.'];
    }

    private function formatStatus(string $status): string
    {
        return match ($status) {
            'accepted' => 'Asistirá',
            'declined' => 'No asistirá',
            'maybe' => 'Pendiente de confirmar',
            default => 'Pendiente',
        };
    }

    private function buildHtml(array $event, array $payload, string $statusLabel): string
    {
        $eventTitle = $event['couple_title'] ?? 'Nuestro evento';
        $eventDate = $event['event_date_start'] ?? '';
        $eventLocation = $event['venue_name'] ?? ($event['venue_address'] ?? '');
        $guestName = $payload['name'] ?? '';
        $message = $payload['message'] ?? '';
        $song = $payload['song_request'] ?? '';

        $lines = [
            '<h2>Confirmación RSVP</h2>',
            '<p>Hola ' . esc($guestName) . ',</p>',
            '<p>Gracias por responder a la invitación de <strong>' . esc((string) $eventTitle) . '</strong>.</p>',
            '<p><strong>Estado:</strong> ' . esc($statusLabel) . '</p>',
        ];

        if ($eventDate !== '') {
            $lines[] = '<p><strong>Fecha:</strong> ' . esc((string) $eventDate) . '</p>';
        }

        if ($eventLocation !== '') {
            $lines[] = '<p><strong>Ubicación:</strong> ' . esc((string) $eventLocation) . '</p>';
        }

        if ($message !== '') {
            $lines[] = '<p><strong>Mensaje:</strong> ' . esc((string) $message) . '</p>';
        }

        if ($song !== '') {
            $lines[] = '<p><strong>Canción sugerida:</strong> ' . esc((string) $song) . '</p>';
        }

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
