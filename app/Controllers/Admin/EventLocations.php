<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventLocationModel;
use App\Models\EventModel;

class EventLocations extends BaseController
{
    protected EventModel $eventModel;
    protected EventLocationModel $locationModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->locationModel = new EventLocationModel();
    }

    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        $locations = $this->locationModel->getByEvent($eventId);

        return view('admin/locations/index', [
            'pageTitle' => 'Ubicaciones: ' . $event['couple_title'],
            'event' => $event,
            'locations' => $locations,
        ]);
    }

    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'code' => 'required|max_length[50]',
            'name' => 'required|max_length[255]',
            'address' => 'permit_empty|max_length[500]',
            'geo_lat' => 'permit_empty|decimal',
            'geo_lng' => 'permit_empty|decimal',
            'maps_url' => 'permit_empty|valid_url',
            'waze_url' => 'permit_empty|valid_url',
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
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'address' => $this->request->getPost('address'),
            'geo_lat' => $this->request->getPost('geo_lat') ?: null,
            'geo_lng' => $this->request->getPost('geo_lng') ?: null,
            'maps_url' => $this->request->getPost('maps_url'),
            'waze_url' => $this->request->getPost('waze_url'),
            'image_url' => $this->request->getPost('image_url'),
            'notes' => $this->request->getPost('notes'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
        ];

        $locationId = $this->locationModel->createLocation($data);

        if ($locationId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Ubicación creada correctamente.',
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'No se pudo crear la ubicación.',
        ]);
    }

    public function update(string $eventId, string $locationId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $location = $this->locationModel->find($locationId);
        if (!$location || $location['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ubicación no encontrada.']);
        }

        $rules = [
            'code' => 'required|max_length[50]',
            'name' => 'required|max_length[255]',
            'address' => 'permit_empty|max_length[500]',
            'geo_lat' => 'permit_empty|decimal',
            'geo_lng' => 'permit_empty|decimal',
            'maps_url' => 'permit_empty|valid_url',
            'waze_url' => 'permit_empty|valid_url',
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
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'address' => $this->request->getPost('address'),
            'geo_lat' => $this->request->getPost('geo_lat') ?: null,
            'geo_lng' => $this->request->getPost('geo_lng') ?: null,
            'maps_url' => $this->request->getPost('maps_url'),
            'waze_url' => $this->request->getPost('waze_url'),
            'image_url' => $this->request->getPost('image_url'),
            'notes' => $this->request->getPost('notes'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
        ];

        $this->locationModel->update($locationId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Ubicación actualizada correctamente.',
        ]);
    }

    public function delete(string $eventId, string $locationId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $location = $this->locationModel->find($locationId);
        if (!$location || $location['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ubicación no encontrada.']);
        }

        $this->locationModel->delete($locationId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Ubicación eliminada correctamente.',
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
