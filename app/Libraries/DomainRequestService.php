<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Enums\DomainRequestStatus;
use App\Models\EventCustomDomainModel;
use App\Models\EventModel;
use App\Models\UserModel;
use Config\Resend;

class DomainRequestService
{
    public function __construct(
        private EventCustomDomainModel $domainModel,
        private EventModel $eventModel,
        private Resend $resendConfig = new Resend()
    ) {
    }

    public function createRequest(string $eventId, string $domain, ?string $requestedByUserId): array
    {
        $normalizedDomain = $this->normalizeDomain($domain);

        if (!$this->isValidDomain($normalizedDomain)) {
            return [
                'success' => false,
                'errors' => [
                    'domain' => 'El formato del dominio no es válido. Usa: ejemplo.com (sin http://, https:// ni rutas).',
                ],
            ];
        }

        if ($this->domainModel->findActiveRequestByEvent($eventId) !== null) {
            return [
                'success' => false,
                'message' => 'Ya existe una solicitud de dominio en proceso para este evento.',
            ];
        }

        if ($this->domainModel->existsByDomain($normalizedDomain)) {
            return [
                'success' => false,
                'errors' => [
                    'domain' => 'Este dominio ya fue solicitado por otro evento.',
                ],
            ];
        }

        $payload = [
            'id' => UserModel::generateUUID(),
            'event_id' => $eventId,
            'domain' => $normalizedDomain,
            'status' => DomainRequestStatus::Requested->value,
            'requested_by_user_id' => $requestedByUserId,
            'admin_notes' => null,
            'dns_configured_at' => null,
        ];

        if (!$this->domainModel->insert($payload)) {
            return [
                'success' => false,
                'message' => 'Error al procesar tu solicitud. Intenta nuevamente.',
                'errors' => $this->domainModel->errors(),
            ];
        }

        $this->sendDomainRequestNotification($eventId, $normalizedDomain);

        return [
            'success' => true,
            'message' => '¡Solicitud enviada! Recibirás una notificación cuando tu dominio esté listo. Costo: $1,200 MXN.',
        ];
    }

    public function updateRequestStatus(string $eventId, DomainRequestStatus $status, ?string $adminMessage): array
    {
        $domainRequest = $this->domainModel->findLatestByEvent($eventId);
        if ($domainRequest === null) {
            return ['success' => false, 'message' => 'No existe solicitud de dominio para este evento.'];
        }

        $updateData = [
            'status' => $status->value,
            'admin_notes' => $adminMessage !== null && trim($adminMessage) !== '' ? trim($adminMessage) : null,
        ];

        if ($status === DomainRequestStatus::Completed && $domainRequest->status !== DomainRequestStatus::Completed->value) {
            $updateData['dns_configured_at'] = date('Y-m-d H:i:s');
        }

        if (!$this->domainModel->update($domainRequest->id, $updateData)) {
            return [
                'success' => false,
                'message' => 'No se pudo actualizar la solicitud de dominio.',
                'errors' => $this->domainModel->errors(),
            ];
        }

        return ['success' => true, 'message' => 'Estado actualizado correctamente.'];
    }

    public function cancelRequested(string $eventId): array
    {
        $domainRequest = $this->domainModel->findLatestByEvent($eventId);
        if ($domainRequest === null) {
            return ['success' => false, 'message' => 'No existe solicitud de dominio.'];
        }

        if ($domainRequest->status !== DomainRequestStatus::Requested->value) {
            return [
                'success' => false,
                'message' => 'Solo puedes cancelar solicitudes que aún no han sido procesadas.',
            ];
        }

        if (!$this->domainModel->delete($domainRequest->id)) {
            return ['success' => false, 'message' => 'No se pudo cancelar la solicitud.'];
        }

        return ['success' => true, 'message' => 'Solicitud cancelada correctamente.'];
    }

    private function normalizeDomain(string $domain): string
    {
        return strtolower(trim($domain));
    }

    private function isValidDomain(string $domain): bool
    {
        if ($domain === '' || preg_match('/^https?:\/\//i', $domain) === 1 || str_contains($domain, '/')) {
            return false;
        }

        return preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $domain) === 1;
    }

    private function sendDomainRequestNotification(string $eventId, string $domain): void
    {
        $event = $this->eventModel->find($eventId);
        if ($event === null) {
            return;
        }

        $notifyTo = (string) (env('DOMAINS_NOTIFY_TO') ?? 'cesar@13bodas.com');
        $requestedBy = (string) (session()->get('user_email') ?? 'Sistema');

        $html = view('emails/domain_request', [
            'couple_title' => $event['couple_title'] ?? '',
            'event_id' => $eventId,
            'slug' => $event['slug'] ?? '',
            'event_date' => !empty($event['event_date_start']) ? date('d/m/Y H:i', strtotime((string) $event['event_date_start'])) : 'Sin fecha',
            'domain_requested' => $domain,
            'requested_by' => $requestedBy,
            'link' => base_url('admin/events/' . $eventId . '/domains'),
            'price' => '$1,200 MXN',
        ]);

        $payload = [
            'from' => trim($this->resendConfig->fromName . ' <' . $this->resendConfig->fromEmail . '>'),
            'to' => [$notifyTo],
            'subject' => '🌐 Nueva Solicitud de Dominio - ' . (string) ($event['couple_title'] ?? 'Evento'),
            'html' => $html,
        ];

        $response = $this->sendEmail($payload);
        if (!$response['success']) {
            log_message('error', 'Error al enviar email de dominio: ' . $response['message']);
        }
    }

    private function sendEmail(array $payload): array
    {
        $ch = curl_init(rtrim($this->resendConfig->apiUrl, '/') . '/emails');
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
                'Authorization: Bearer ' . $this->resendConfig->apiKey,
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
