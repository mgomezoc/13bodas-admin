<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventLocationModel;
use App\Models\EventModel;
use App\Models\EventScheduleItemModel;

class EventSchedule extends BaseController
{
    protected EventModel $eventModel;
    protected EventScheduleItemModel $scheduleModel;
    protected EventLocationModel $locationModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->scheduleModel = new EventScheduleItemModel();
        $this->locationModel = new EventLocationModel();
    }

    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        $items = $this->scheduleModel->getByEvent($eventId);
        $locations = $this->locationModel->getByEvent($eventId);

        $locationMap = array_column($locations, 'name', 'id');

        return view('admin/schedule/index', [
            'pageTitle' => 'Agenda: ' . $event['couple_title'],
            'event' => $event,
            'items' => $items,
            'locations' => $locations,
            'locationMap' => $locationMap,
        ]);
    }

    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'title' => 'required|max_length[255]',
            'location_id' => 'permit_empty|max_length[64]',
            'starts_at' => 'required',
            'ends_at' => 'permit_empty',
            'icon' => 'permit_empty|max_length[100]',
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
            'location_id' => $this->request->getPost('location_id') ?: null,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'starts_at' => $this->request->getPost('starts_at'),
            'ends_at' => $this->request->getPost('ends_at') ?: null,
            'icon' => $this->request->getPost('icon'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_visible' => $this->request->getPost('is_visible') ? 1 : 0,
        ];

        $itemId = $this->scheduleModel->createItem($data);

        if ($itemId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Actividad creada correctamente.',
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'No se pudo crear la actividad.',
        ]);
    }

    public function update(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->scheduleModel->find($itemId);
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Actividad no encontrada.']);
        }

        $rules = [
            'title' => 'required|max_length[255]',
            'location_id' => 'permit_empty|max_length[64]',
            'starts_at' => 'required',
            'ends_at' => 'permit_empty',
            'icon' => 'permit_empty|max_length[100]',
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
            'location_id' => $this->request->getPost('location_id') ?: null,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'starts_at' => $this->request->getPost('starts_at'),
            'ends_at' => $this->request->getPost('ends_at') ?: null,
            'icon' => $this->request->getPost('icon'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_visible' => $this->request->getPost('is_visible') ? 1 : 0,
        ];

        $this->scheduleModel->update($itemId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Actividad actualizada correctamente.',
        ]);
    }

    public function delete(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->scheduleModel->find($itemId);
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Actividad no encontrada.']);
        }

        $this->scheduleModel->delete($itemId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Actividad eliminada correctamente.',
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
