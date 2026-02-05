<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventCustomDomainModel;
use App\Models\EventModel;

class EventCustomDomains extends BaseController
{
    protected EventModel $eventModel;
    protected EventCustomDomainModel $domainModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->domainModel = new EventCustomDomainModel();
    }

    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId) || !$this->isAdmin()) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Sin acceso.');
        }

        $domains = $this->domainModel->getByEvent($eventId);

        return view('admin/domains/index', [
            'pageTitle' => 'Dominios: ' . $event['couple_title'],
            'event' => $event,
            'domains' => $domains,
        ]);
    }

    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId) || !$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'domain' => 'required|max_length[255]',
            'status' => 'required|in_list[pending_dns,active,disabled]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $data = [
            'event_id' => $eventId,
            'domain' => $this->request->getPost('domain'),
            'status' => $this->request->getPost('status'),
        ];

        $domainId = $this->domainModel->createDomain($data);

        if ($domainId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Dominio agregado correctamente.',
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'No se pudo crear el dominio.',
        ]);
    }

    public function update(string $eventId, string $domainId)
    {
        if (!$this->canAccessEvent($eventId) || !$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $domain = $this->domainModel->find($domainId);
        if (!$domain || $domain['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dominio no encontrado.']);
        }

        $rules = [
            'domain' => 'required|max_length[255]',
            'status' => 'required|in_list[pending_dns,active,disabled]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $data = [
            'domain' => $this->request->getPost('domain'),
            'status' => $this->request->getPost('status'),
        ];

        $this->domainModel->update($domainId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dominio actualizado correctamente.',
        ]);
    }

    public function delete(string $eventId, string $domainId)
    {
        if (!$this->canAccessEvent($eventId) || !$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $domain = $this->domainModel->find($domainId);
        if (!$domain || $domain['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dominio no encontrado.']);
        }

        $this->domainModel->delete($domainId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dominio eliminado correctamente.',
        ]);
    }

    protected function canAccessEvent(string $eventId): bool
    {
        $session = session();
        $userRoles = $session->get('user_roles') ?? [];

        if (in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true) || in_array('staff', $userRoles, true)) {
            return true;
        }

        $clientId = $session->get('client_id');
        if ($clientId) {
            $event = $this->eventModel->find($eventId);
            return $event && $event['client_id'] === $clientId;
        }

        return false;
    }

    protected function isAdmin(): bool
    {
        $userRoles = session()->get('user_roles') ?? [];
        return in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);
    }
}
