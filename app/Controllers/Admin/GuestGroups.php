<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\GuestGroupModel;

class GuestGroups extends BaseController
{
    protected EventModel $eventModel;
    protected GuestGroupModel $groupModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->groupModel = new GuestGroupModel();
    }

    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        $groups = $this->groupModel->getByEventWithGuestCount($eventId);

        return view('admin/groups/index', [
            'pageTitle' => 'Grupos: ' . $event['couple_title'],
            'event' => $event,
            'groups' => $groups,
        ]);
    }

    public function list(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['total' => 0, 'rows' => []]);
        }

        $groups = $this->groupModel->getByEventWithGuestCount($eventId);

        return $this->response->setJSON([
            'total' => count($groups),
            'rows' => $groups,
        ]);
    }

    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'group_name' => 'required|max_length[255]',
            'access_code' => 'permit_empty|max_length[20]',
            'max_additional_guests' => 'permit_empty|integer',
            'current_status' => 'permit_empty|in_list[invited,viewed,partial,responded]',
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
            'group_name' => $this->request->getPost('group_name'),
            'max_additional_guests' => $this->request->getPost('max_additional_guests') ?: 0,
            'is_vip' => $this->request->getPost('is_vip') ? 1 : 0,
            'current_status' => $this->request->getPost('current_status') ?: 'invited',
            'invited_at' => $this->request->getPost('invited_at') ?: null,
            'first_viewed_at' => $this->request->getPost('first_viewed_at') ?: null,
            'last_viewed_at' => $this->request->getPost('last_viewed_at') ?: null,
            'responded_at' => $this->request->getPost('responded_at') ?: null,
        ];

        $accessCode = trim((string) $this->request->getPost('access_code'));
        if ($accessCode !== '') {
            $data['access_code'] = $accessCode;
        }

        $groupId = $this->groupModel->createGroup($data);

        if ($groupId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Grupo creado correctamente.',
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'No se pudo crear el grupo.',
        ]);
    }

    public function update(string $eventId, string $groupId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $group = $this->groupModel->find($groupId);
        if (!$group || $group['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Grupo no encontrado.']);
        }

        $rules = [
            'group_name' => 'required|max_length[255]',
            'access_code' => 'permit_empty|max_length[20]',
            'max_additional_guests' => 'permit_empty|integer',
            'current_status' => 'permit_empty|in_list[invited,viewed,partial,responded]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $data = [
            'group_name' => $this->request->getPost('group_name'),
            'access_code' => $this->request->getPost('access_code') ?: $group['access_code'],
            'max_additional_guests' => $this->request->getPost('max_additional_guests') ?: 0,
            'is_vip' => $this->request->getPost('is_vip') ? 1 : 0,
            'current_status' => $this->request->getPost('current_status') ?: 'invited',
            'invited_at' => $this->request->getPost('invited_at') ?: null,
            'first_viewed_at' => $this->request->getPost('first_viewed_at') ?: null,
            'last_viewed_at' => $this->request->getPost('last_viewed_at') ?: null,
            'responded_at' => $this->request->getPost('responded_at') ?: null,
        ];

        $this->groupModel->update($groupId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Grupo actualizado correctamente.',
        ]);
    }

    public function delete(string $eventId, string $groupId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $group = $this->groupModel->find($groupId);
        if (!$group || $group['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Grupo no encontrado.']);
        }

        $this->groupModel->delete($groupId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Grupo eliminado correctamente.',
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
}
