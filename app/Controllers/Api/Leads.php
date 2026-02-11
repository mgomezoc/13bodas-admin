<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\PublicLeadMailer;
use App\Models\LeadModel;
use CodeIgniter\HTTP\ResponseInterface;

class Leads extends BaseController
{
    public function __construct(
        private readonly LeadModel $leadModel = new LeadModel(),
        private readonly PublicLeadMailer $publicLeadMailer = new PublicLeadMailer(),
    ) {
    }

    public function store(): ResponseInterface
    {
        $rules = [
            'nombre' => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email|max_length[120]',
            'telefono' => 'permit_empty|max_length[30]',
            'tipo_evento' => 'required|in_list[boda,xv,bautizo,aniversario,corporativo,otro]',
            'fecha_evento' => 'permit_empty|valid_date[Y-m-d]',
            'paquete_interes' => 'permit_empty|max_length[60]',
            'mensaje' => 'permit_empty|max_length[2000]',
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithFailure('Revisa los datos del formulario.', 422, $this->validator?->getErrors() ?? []);
        }

        $leadData = [
            'full_name' => (string) $this->request->getPost('nombre'),
            'email' => (string) $this->request->getPost('email'),
            'phone' => (string) $this->request->getPost('telefono'),
            'event_date' => (string) $this->request->getPost('fecha_evento'),
            'source' => 'public_home_form',
            'message' => $this->buildLeadMessage(),
        ];

        $leadId = $this->leadModel->createLead($leadData);

        if ($leadId === null) {
            return $this->respondWithFailure('No pudimos registrar tu solicitud en este momento.', 500);
        }

        $notificationResult = $this->publicLeadMailer->sendLeadNotification([
            'lead_id' => $leadId,
            'full_name' => $leadData['full_name'],
            'email' => $leadData['email'],
            'phone' => $leadData['phone'],
            'event_type' => (string) $this->request->getPost('tipo_evento'),
            'event_date' => $leadData['event_date'] !== '' ? $leadData['event_date'] : 'No especificada',
            'package_interest' => (string) $this->request->getPost('paquete_interes'),
            'source' => $leadData['source'],
            'message' => (string) $this->request->getPost('mensaje'),
        ]);

        if (!$notificationResult['success']) {
            log_message('warning', 'Lead notification failed: {message}', ['message' => $notificationResult['message']]);
        }

        return $this->respondWithSuccess('Gracias, tu solicitud fue enviada. Te contactaremos pronto.', [
            'lead_id' => $leadId,
            'notification_sent' => $notificationResult['success'],
        ]);
    }

    private function buildLeadMessage(): string
    {
        $eventType = (string) $this->request->getPost('tipo_evento');
        $eventDate = (string) $this->request->getPost('fecha_evento');
        $packageInterest = (string) $this->request->getPost('paquete_interes');
        $rawMessage = (string) $this->request->getPost('mensaje');

        $parts = [
            'Tipo de evento: ' . ($eventType !== '' ? $eventType : 'No especificado'),
            'Fecha estimada: ' . ($eventDate !== '' ? $eventDate : 'No especificada'),
            'Paquete de interés: ' . ($packageInterest !== '' ? $packageInterest : 'No especificado'),
            'Mensaje: ' . ($rawMessage !== '' ? $rawMessage : 'Sin mensaje'),
        ];

        return implode("\n", $parts);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $errors
     */
    private function respondWithFailure(string $message, int $statusCode, array $errors = []): ResponseInterface
    {
        if ($this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json')) {
            return $this->response->setStatusCode($statusCode)->setJSON([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ]);
        }

        return redirect()->to(site_url(route_to('home')) . '#contacto')
            ->withInput()
            ->with('contact_error', $message)
            ->with('contact_errors', $errors);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function respondWithSuccess(string $message, array $data = []): ResponseInterface
    {
        if ($this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json')) {
            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ]);
        }

        return redirect()->to(site_url(route_to('home.thanks')))
            ->with('contact_success', $message);
    }
}
