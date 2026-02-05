<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\WeddingPartyMemberModel;

class WeddingParty extends BaseController
{
    protected EventModel $eventModel;
    protected WeddingPartyMemberModel $partyModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->partyModel = new WeddingPartyMemberModel();
    }

    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        $members = $this->partyModel->getByEvent($eventId);

        return view('admin/party/index', [
            'pageTitle' => 'Cortejo: ' . $event['couple_title'],
            'event' => $event,
            'members' => $members,
            'categories' => WeddingPartyMemberModel::CATEGORIES,
        ]);
    }

    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'full_name' => 'required|max_length[255]',
            'role' => 'permit_empty|max_length[120]',
            'category' => 'required|in_list[bride_side,groom_side,officiant,other]',
            'image_url' => 'permit_empty|valid_url',
            'display_order' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $socialLinks = $this->request->getPost('social_links');
        $socialLinks = $socialLinks !== null ? trim((string) $socialLinks) : '';
        if ($socialLinks !== '') {
            json_decode($socialLinks);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'social_links debe ser JSON válido.',
                ]);
            }
        }

        $data = [
            'event_id' => $eventId,
            'full_name' => $this->request->getPost('full_name'),
            'role' => $this->request->getPost('role'),
            'category' => $this->request->getPost('category'),
            'bio' => $this->request->getPost('bio'),
            'image_url' => $this->request->getPost('image_url'),
            'social_links' => $socialLinks !== '' ? $socialLinks : null,
            'display_order' => $this->request->getPost('display_order') ?: null,
        ];

        $memberId = $this->partyModel->createMember($data);

        if ($memberId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Miembro agregado correctamente.',
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'No se pudo agregar el miembro.',
        ]);
    }

    public function update(string $eventId, string $memberId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $member = $this->partyModel->find($memberId);
        if (!$member || $member['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Miembro no encontrado.']);
        }

        $rules = [
            'full_name' => 'required|max_length[255]',
            'role' => 'permit_empty|max_length[120]',
            'category' => 'required|in_list[bride_side,groom_side,officiant,other]',
            'image_url' => 'permit_empty|valid_url',
            'display_order' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $socialLinks = $this->request->getPost('social_links');
        $socialLinks = $socialLinks !== null ? trim((string) $socialLinks) : '';
        if ($socialLinks !== '') {
            json_decode($socialLinks);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'social_links debe ser JSON válido.',
                ]);
            }
        }

        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'role' => $this->request->getPost('role'),
            'category' => $this->request->getPost('category'),
            'bio' => $this->request->getPost('bio'),
            'image_url' => $this->request->getPost('image_url'),
            'social_links' => $socialLinks !== '' ? $socialLinks : null,
            'display_order' => $this->request->getPost('display_order') ?: null,
        ];

        $this->partyModel->update($memberId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Miembro actualizado correctamente.',
        ]);
    }

    public function delete(string $eventId, string $memberId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $member = $this->partyModel->find($memberId);
        if (!$member || $member['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Miembro no encontrado.']);
        }

        $this->partyModel->delete($memberId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Miembro eliminado correctamente.',
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
