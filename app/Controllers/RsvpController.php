<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\RsvpSubmissionService;
use App\Models\EventModel;
use CodeIgniter\HTTP\ResponseInterface;

class RsvpController extends BaseController
{
    public function __construct(
        private EventModel $eventModel = new EventModel(),
        private RsvpSubmissionService $rsvpService = new RsvpSubmissionService()
    ) {
    }

    public function submit(string $slug): ResponseInterface
    {
        $event = $this->eventModel->where('slug', $slug)->first();
        if (!$event) {
            return $this->respond($slug, ['success' => false, 'message' => 'Evento no encontrado.'], 404);
        }

        $payload = $this->normalizePayload();

        $rules = [
            'name' => 'required|min_length[2]',
            'email' => 'required|valid_email',
            'attending' => 'required|in_list[accepted,declined,maybe]',
        ];

        if (!$this->validateData($payload, $rules)) {
            $message = implode(' ', $this->validator->getErrors());
            return $this->respond($slug, ['success' => false, 'message' => $message], 422);
        }

        $result = $this->rsvpService->submit($event, $payload);
        $status = $result['success'] ? 200 : 400;

        return $this->respond($slug, $result, $status);
    }

    private function normalizePayload(): array
    {
        $attending = strtolower((string) ($this->request->getPost('attending') ?? ''));
        $attending = match ($attending) {
            'yes' => 'accepted',
            'no' => 'declined',
            default => $attending,
        };

        return [
            'name' => trim((string) ($this->request->getPost('name') ?? $this->request->getPost('guest_name'))),
            'email' => trim((string) ($this->request->getPost('email') ?? $this->request->getPost('guest_email'))),
            'phone' => trim((string) ($this->request->getPost('phone') ?? $this->request->getPost('guest_phone'))),
            'attending' => $attending === '' ? 'maybe' : $attending,
            'message' => trim((string) ($this->request->getPost('message') ?? '')),
            'song_request' => trim((string) ($this->request->getPost('song_request') ?? '')),
            'guests' => trim((string) ($this->request->getPost('guests') ?? $this->request->getPost('guest_count') ?? '')),
            'meal_option' => trim((string) ($this->request->getPost('meal_option') ?? '')),
            'guest_code' => trim((string) ($this->request->getPost('guest_code') ?? '')),
        ];
    }

    private function respond(string $slug, array $payload, int $status): ResponseInterface
    {
        session()->setFlashdata('rsvp_status', [
            'type' => $payload['success'] ? 'success' : 'error',
            'message' => $payload['message'] ?? 'No se pudo procesar la solicitud.',
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode($status)->setJSON($payload);
        }

        return redirect()->to(route_to('invitation.view', $slug));
    }
}
