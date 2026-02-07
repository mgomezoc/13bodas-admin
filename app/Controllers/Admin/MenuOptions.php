<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\MenuOptionModel;

class MenuOptions extends BaseController
{
    protected $eventModel;
    protected $menuModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->menuModel = new MenuOptionModel();
    }

    /**
     * Opciones de menú del evento
     */
    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Evento no encontrado.');
        }

        $options = $this->menuModel->where('event_id', $eventId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        $data = [
            'pageTitle' => 'Opciones de Menú: ' . $event['couple_title'],
            'event' => $event,
            'options' => $options,
            'hasIsActive' => $this->menuModel->hasColumn('is_active'),
        ];

        return view('admin/menu/index', $data);
    }

    /**
     * Guardar nueva opción
     */
    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'name' => 'required|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nombre es requerido.']);
        }

        $optionData = [
            'event_id' => $eventId,
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_vegan' => $this->request->getPost('is_vegan') ? 1 : 0,
            'is_gluten_free' => $this->request->getPost('is_gluten_free') ? 1 : 0,
            'is_kid_friendly' => $this->request->getPost('is_kid_friendly') ? 1 : 0,
            'sort_order' => $this->menuModel->where('event_id', $eventId)->countAllResults() + 1
        ];

        $optionId = $this->menuModel->createOption($optionData);

        if ($optionId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Opción de menú agregada.',
                'option_id' => $optionId
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar.']);
    }

    /**
     * Actualizar opción
     */
    public function update(string $eventId, int $optionId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $option = $this->menuModel->find($optionId);
        if (!$option || $option['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Opción no encontrada.']);
        }

        $optionData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_vegan' => $this->request->getPost('is_vegan') ? 1 : 0,
            'is_gluten_free' => $this->request->getPost('is_gluten_free') ? 1 : 0,
            'is_kid_friendly' => $this->request->getPost('is_kid_friendly') ? 1 : 0,
        ];

        if ($this->menuModel->hasColumn('is_active')) {
            $optionData['is_active'] = $this->request->getPost('is_active') ? 1 : 0;
        }

        $this->menuModel->update($optionId, $optionData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Opción actualizada correctamente.'
        ]);
    }

    /**
     * Eliminar opción
     */
    public function delete(string $eventId, int $optionId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $option = $this->menuModel->find($optionId);
        if (!$option || $option['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Opción no encontrada.']);
        }

        $this->menuModel->delete($optionId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Opción eliminada correctamente.'
        ]);
    }

    /**
     * Verificar acceso al evento
     */
    protected function canAccessEvent(string $eventId): bool
    {
        $session = session();
        $userRoles = $session->get('user_roles') ?? [];
        
        if (in_array('superadmin', $userRoles) || in_array('admin', $userRoles) || in_array('staff', $userRoles)) {
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
