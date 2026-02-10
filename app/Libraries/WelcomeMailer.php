<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\Resend;

class WelcomeMailer
{
    public function __construct(private readonly Resend $config = new Resend())
    {
    }

    public function sendRegistrationWelcome(array $payload): array
    {
        $toEmail = trim((string) ($payload['email'] ?? ''));

        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Correo inválido para bienvenida.'];
        }

        if (trim($this->config->apiKey) === '') {
            return ['success' => false, 'message' => 'RESEND_API_KEY no configurado.'];
        }

        $html = view('emails/welcome_register', [
            'name' => (string) ($payload['name'] ?? ''),
            'eventTitle' => (string) ($payload['event_title'] ?? ''),
            'eventDate' => (string) ($payload['event_date'] ?? ''),
            'dashboardUrl' => (string) ($payload['dashboard_url'] ?? base_url('admin/dashboard')),
            'eventEditUrl' => (string) ($payload['event_edit_url'] ?? base_url('admin/events')),
            'checkoutUrl' => (string) ($payload['checkout_url'] ?? ''),
            'supportWhatsappUrl' => 'https://wa.me/528115247741',
        ]);

        $subject = '¡Bienvenido a 13Bodas! Tu evento demo ya está listo';

        return $this->sendEmail([
            'from' => trim($this->config->fromName . ' <' . $this->config->fromEmail . '>'),
            'to' => [$toEmail],
            'subject' => $subject,
            'html' => $html,
        ]);
    }

    private function sendEmail(array $payload): array
    {
        $ch = curl_init(rtrim($this->config->apiUrl, '/') . '/emails');

        if ($ch === false) {
            return ['success' => false, 'message' => 'No se pudo inicializar el envío.'];
        }

        $jsonPayload = json_encode($payload);
        if ($jsonPayload === false) {
            return ['success' => false, 'message' => 'No se pudo serializar el correo.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->config->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_TIMEOUT => 15,
        ]);

        $responseBody = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false) {
            return ['success' => false, 'message' => 'Error al enviar correo: ' . $error];
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            return ['success' => false, 'message' => 'Proveedor de correo respondió con estado ' . $statusCode . '.'];
        }

        return ['success' => true, 'message' => 'Correo enviado.'];
    }
}
