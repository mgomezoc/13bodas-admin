<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Enums\DomainRequestStatus;
use App\Libraries\DomainRequestService;
use App\Models\EventCustomDomainModel;
use App\Models\EventModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class EventDomainsController extends BaseController
{
    public function __construct(
        private ?EventCustomDomainModel $domainModel = null,
        private ?EventModel $eventModel = null,
        private ?DomainRequestService $domainRequestService = null
    ) {
        $this->domainModel = $this->domainModel ?? new EventCustomDomainModel();
        $this->eventModel = $this->eventModel ?? new EventModel();
        $this->domainRequestService = $this->domainRequestService ?? new DomainRequestService($this->domainModel, $this->eventModel);
    }

    public function index(string $eventId): string|RedirectResponse
    {
        $event = $this->eventModel->find($eventId);

        if ($event === null || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Acceso denegado.');
        }

        $domainRequestEntity = $this->domainModel->findLatestByEvent($eventId);
        $domainRequest = $domainRequestEntity?->toArray();

        return view('admin/events/domains/index', [
            'pageTitle' => 'Dominio Personalizado',
            'event' => $event,
            'domainRequest' => $domainRequest,
            'isAdmin' => $this->isAdmin(),
            'fixedPrice' => 1200,
        ]);
    }

    public function request(string $eventId): ResponseInterface|RedirectResponse
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.',
            ]);
        }

        $domain = (string) $this->request->getPost('domain');
        $requestedByUserId = session()->get('user_id') !== null ? (string) session()->get('user_id') : null;

        $result = $this->domainRequestService->createRequest($eventId, $domain, $requestedByUserId);

        return $this->response->setJSON($result);
    }

    public function update(string $eventId): ResponseInterface|RedirectResponse
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        if (!$this->isAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solo administradores pueden actualizar el estado.',
            ]);
        }

        $rules = [
            'status' => 'required|in_list[' . implode(',', DomainRequestStatus::values()) . ']',
            'admin_message' => 'permit_empty|max_length[2000]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $status = DomainRequestStatus::from((string) $this->request->getPost('status'));
        $adminMessage = $this->request->getPost('admin_message');
        $adminMessage = is_string($adminMessage) ? $adminMessage : null;

        $result = $this->domainRequestService->updateRequestStatus($eventId, $status, $adminMessage);

        return $this->response->setJSON($result);
    }

    public function cancel(string $eventId): ResponseInterface|RedirectResponse
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.',
            ]);
        }

        $result = $this->domainRequestService->cancelRequested($eventId);

        return $this->response->setJSON($result);
    }

    private function canAccessEvent(string $eventId): bool
    {
        $session = session();
        $userRoles = $session->get('user_roles') ?? [];

        if (in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true) || in_array('staff', $userRoles, true)) {
            return true;
        }

        $clientId = $session->get('client_id');
        if ($clientId !== null) {
            $event = $this->eventModel->find($eventId);
            return $event !== null && (string) ($event['client_id'] ?? '') === (string) $clientId;
        }

        return false;
    }

    private function isAdmin(): bool
    {
        $userRoles = session()->get('user_roles') ?? [];

        return in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);
    }
}
