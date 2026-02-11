<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\Resend;

class PublicLeadMailer
{
    private string $notificationEmail;

    public function __construct(
        private readonly Resend $config = new Resend(),
    ) {
        $this->notificationEmail = trim((string) (env('LEADS_NOTIFICATION_EMAIL') ?? '0013zkr@gmail.com'));
    }

    /**
     * @param array<string, string> $leadData
     * @return array{success:bool, message:string}
     */
    public function sendLeadNotification(array $leadData): array
    {
        if (!filter_var($this->notificationEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Correo destino de notificaciones inválido.'];
        }

        if (trim($this->config->apiKey) === '') {
            return ['success' => false, 'message' => 'RESEND_API_KEY no configurado.'];
        }

        $html = view('emails/public_lead_notification', [
            'leadData' => $leadData,
            'createdAt' => date('Y-m-d H:i:s'),
            'dashboardLeadsUrl' => base_url('admin/leads'),
        ]);

        $subject = 'Nuevo lead público en 13Bodas: ' . ($leadData['full_name'] ?? 'Sin nombre');

        return $this->sendEmail([
            'from' => trim($this->config->fromName . ' <' . $this->config->fromEmail . '>'),
            'to' => [$this->notificationEmail],
            'reply_to' => [
                [
                    'email' => (string) ($leadData['email'] ?? $this->notificationEmail),
                    'name' => (string) ($leadData['full_name'] ?? 'Lead 13Bodas'),
                ],
            ],
            'subject' => $subject,
            'html' => $html,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{success:bool, message:string}
     */
    private function sendEmail(array $payload): array
    {
        $ch = curl_init(rtrim($this->config->apiUrl, '/') . '/emails');

        if ($ch === false) {
            return ['success' => false, 'message' => 'No se pudo inicializar el envío de notificación.'];
        }

        $jsonPayload = json_encode($payload);

        if ($jsonPayload === false) {
            return ['success' => false, 'message' => 'No se pudo serializar la notificación.'];
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
            return ['success' => false, 'message' => 'Error al enviar notificación: ' . $error];
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            return ['success' => false, 'message' => 'Proveedor respondió con estado ' . $statusCode . '.'];
        }

        return ['success' => true, 'message' => 'Notificación enviada correctamente.'];
    }
}
