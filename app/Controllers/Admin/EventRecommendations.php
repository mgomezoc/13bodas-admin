<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\EventRecommendationModel;

class EventRecommendations extends BaseController
{
    protected EventModel $eventModel;
    protected EventRecommendationModel $recommendationModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->recommendationModel = new EventRecommendationModel();
    }

    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        $items = $this->recommendationModel->getByEvent($eventId);

        return view('admin/recommendations/index', [
            'pageTitle' => 'Recomendaciones: ' . $event['couple_title'],
            'event' => $event,
            'items' => $items,
        ]);
    }

    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'type' => 'required|in_list[hotel,transport,restaurant,other]',
            'title' => 'required|max_length[255]',
            'url' => 'permit_empty|valid_url',
            'image_url' => 'permit_empty|valid_url',
            'sort_order' => 'permit_empty|integer',
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
            'type' => $this->request->getPost('type'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'url' => $this->request->getPost('url'),
            'image_url' => $this->request->getPost('image_url'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_visible' => $this->request->getPost('is_visible') ? 1 : 0,
        ];

        $itemId = $this->recommendationModel->createItem($data);

        if ($itemId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Recomendación creada correctamente.',
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'No se pudo crear la recomendación.',
        ]);
    }

    public function update(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->recommendationModel->find($itemId);
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Recomendación no encontrada.']);
        }

        $rules = [
            'type' => 'required|in_list[hotel,transport,restaurant,other]',
            'title' => 'required|max_length[255]',
            'url' => 'permit_empty|valid_url',
            'image_url' => 'permit_empty|valid_url',
            'sort_order' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $data = [
            'type' => $this->request->getPost('type'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'url' => $this->request->getPost('url'),
            'image_url' => $this->request->getPost('image_url'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_visible' => $this->request->getPost('is_visible') ? 1 : 0,
        ];

        $this->recommendationModel->update($itemId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Recomendación actualizada correctamente.',
        ]);
    }

    public function delete(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->recommendationModel->find($itemId);
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Recomendación no encontrada.']);
        }

        $this->recommendationModel->delete($itemId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Recomendación eliminada correctamente.',
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
