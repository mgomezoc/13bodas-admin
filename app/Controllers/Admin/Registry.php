<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\RegistryItemModel;
use App\Models\UserModel;

class Registry extends BaseController
{
    protected $eventModel;
    protected $registryModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->registryModel = new RegistryItemModel();
    }

    /**
     * Lista de regalos del evento
     */
    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Evento no encontrado.');
        }

        $items = $this->registryModel
            ->where('event_id', $eventId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $stats = $this->eventModel->getEventStats($eventId);
        
        // Calcular estadísticas de regalos
        $totalItems = count($items);
        $claimedItems = count(array_filter($items, fn($item) => $item['is_claimed'] == 1));
        $totalValue = array_sum(array_column($items, 'price'));
        $claimedValue = array_sum(array_map(fn($item) => $item['is_claimed'] ? $item['price'] : 0, $items));

        return view('admin/registry/index', [
            'pageTitle' => 'Lista de Regalos: ' . $event['couple_title'],
            'event' => $event,
            'items' => $items,
            'stats' => $stats,
            'registryStats' => [
                'total_items' => $totalItems,
                'claimed_items' => $claimedItems,
                'total_value' => $totalValue,
                'claimed_value' => $claimedValue
            ]
        ]);
    }

    /**
     * Guardar nuevo regalo
     */
    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'name' => 'required|max_length[255]',
            'price' => 'permit_empty|decimal',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $itemId = UserModel::generateUUID();
        
        $data = [
            'id' => $itemId,
            'event_id' => $eventId,
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'price' => $this->request->getPost('price') ?: 0,
            'external_url' => $this->request->getPost('external_url'),
            'image_url' => $this->request->getPost('image_url'),
            'is_fund' => $this->request->getPost('is_fund') ? 1 : 0,
            'fund_goal' => $this->request->getPost('fund_goal') ?: 0,
            'is_claimed' => 0,
            'sort_order' => 999,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->registryModel->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Regalo agregado correctamente.',
            'item' => $data
        ]);
    }

    /**
     * Actualizar regalo
     */
    public function update(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->registryModel->find($itemId);
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Regalo no encontrado.']);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'price' => $this->request->getPost('price') ?: 0,
            'external_url' => $this->request->getPost('external_url'),
            'image_url' => $this->request->getPost('image_url'),
            'is_fund' => $this->request->getPost('is_fund') ? 1 : 0,
            'fund_goal' => $this->request->getPost('fund_goal') ?: 0,
        ];

        $this->registryModel->update($itemId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Regalo actualizado correctamente.'
        ]);
    }

    /**
     * Marcar como reclamado
     */
    public function toggleClaimed(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->registryModel->find($itemId);
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Regalo no encontrado.']);
        }

        $newStatus = $item['is_claimed'] ? 0 : 1;
        $this->registryModel->update($itemId, [
            'is_claimed' => $newStatus,
            'claimed_at' => $newStatus ? date('Y-m-d H:i:s') : null
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => $newStatus ? 'Marcado como reclamado.' : 'Marcado como disponible.',
            'is_claimed' => $newStatus
        ]);
    }

    /**
     * Eliminar regalo
     */
    public function delete(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->registryModel->find($itemId);
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Regalo no encontrado.']);
        }

        $this->registryModel->delete($itemId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Regalo eliminado correctamente.'
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
