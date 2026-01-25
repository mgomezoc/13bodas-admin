<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\RegistryItemModel;

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

        // Obtener items por categoría
        $items = $this->registryModel->getByEvent($eventId);
        
        // Estadísticas
        $stats = $this->registryModel->getStatsByEvent($eventId);

        // Agrupar por categoría
        $itemsByCategory = [];
        foreach ($items as $item) {
            $category = $item['category'] ?: 'Sin categoría';
            if (!isset($itemsByCategory[$category])) {
                $itemsByCategory[$category] = [];
            }
            $itemsByCategory[$category][] = $item;
        }

        $data = [
            'pageTitle' => 'Lista de Regalos: ' . $event['couple_title'],
            'event' => $event,
            'items' => $items,
            'itemsByCategory' => $itemsByCategory,
            'stats' => $stats,
            'categories' => $this->getCategories()
        ];

        return view('admin/registry/index', $data);
    }

    /**
     * API: Lista de items para Bootstrap Table
     */
    public function list(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['total' => 0, 'rows' => []]);
        }

        $items = $this->registryModel->getByEvent($eventId);

        return $this->response->setJSON([
            'total' => count($items),
            'rows' => $items
        ]);
    }

    /**
     * Guardar nuevo item
     */
    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'name' => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $isFund = $this->request->getPost('is_fund') ? 1 : 0;
        $price = $this->request->getPost('price');
        $goalAmount = $this->request->getPost('goal_amount');

        $itemData = [
            'event_id' => $eventId,
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'price' => $isFund ? null : ($price ?: null),
            'external_url' => $this->request->getPost('external_url'),
            'image_url' => $this->request->getPost('image_url'),
            'is_fund' => $isFund,
            'goal_amount' => $isFund ? ($goalAmount ?: null) : null,
            'current_amount' => 0,
            'quantity_requested' => $this->request->getPost('quantity_requested') ?: 1,
            'quantity_fulfilled' => 0,
            'is_priority' => $this->request->getPost('is_priority') ? 1 : 0,
            'is_visible' => 1,
            'sort_order' => $this->registryModel->getNextSortOrder($eventId)
        ];

        $itemId = $this->registryModel->createItem($itemData);

        if ($itemId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Regalo agregado correctamente.',
                'item_id' => $itemId
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error al agregar el regalo.'
        ]);
    }

    /**
     * Obtener item para edición
     */
    public function get(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->registryModel->find($itemId);
        
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item no encontrado.']);
        }

        return $this->response->setJSON([
            'success' => true,
            'item' => $item
        ]);
    }

    /**
     * Actualizar item
     */
    public function update(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->registryModel->find($itemId);
        
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item no encontrado.']);
        }

        $rules = [
            'name' => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $isFund = $this->request->getPost('is_fund') ? 1 : 0;

        $itemData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'price' => $isFund ? null : ($this->request->getPost('price') ?: null),
            'external_url' => $this->request->getPost('external_url'),
            'image_url' => $this->request->getPost('image_url'),
            'is_fund' => $isFund,
            'goal_amount' => $isFund ? ($this->request->getPost('goal_amount') ?: null) : null,
            'quantity_requested' => $this->request->getPost('quantity_requested') ?: 1,
            'is_priority' => $this->request->getPost('is_priority') ? 1 : 0,
            'is_visible' => $this->request->getPost('is_visible') ? 1 : 0,
        ];

        $this->registryModel->update($itemId, $itemData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Regalo actualizado correctamente.'
        ]);
    }

    /**
     * Eliminar item
     */
    public function delete(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->registryModel->find($itemId);
        
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item no encontrado.']);
        }

        $this->registryModel->delete($itemId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Regalo eliminado correctamente.'
        ]);
    }

    /**
     * Marcar como reclamado/comprado
     */
    public function markClaimed(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->registryModel->find($itemId);
        
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item no encontrado.']);
        }

        $quantity = $this->request->getPost('quantity') ?: 1;
        $claimedBy = $this->request->getPost('claimed_by');

        $result = $this->registryModel->markAsClaimed($itemId, $claimedBy, $quantity);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Regalo marcado como reclamado.'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error al marcar el regalo.'
        ]);
    }

    /**
     * Agregar contribución a un fondo
     */
    public function addContribution(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->registryModel->find($itemId);
        
        if (!$item || $item['event_id'] !== $eventId || !$item['is_fund']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Fondo no encontrado.']);
        }

        $amount = floatval($this->request->getPost('amount'));
        $contributorName = $this->request->getPost('contributor_name');

        if ($amount <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Monto inválido.']);
        }

        $result = $this->registryModel->addContribution($itemId, $amount, $contributorName);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Contribución registrada correctamente.'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error al registrar la contribución.'
        ]);
    }

    /**
     * Reordenar items
     */
    public function reorder(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $order = $this->request->getPost('order');
        
        if (!is_array($order)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos inválidos.']);
        }

        foreach ($order as $index => $itemId) {
            $this->registryModel->update($itemId, ['sort_order' => $index]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Orden actualizado.'
        ]);
    }

    /**
     * Categorías predefinidas
     */
    protected function getCategories(): array
    {
        return [
            'Cocina' => 'Cocina',
            'Hogar' => 'Hogar',
            'Recámara' => 'Recámara',
            'Baño' => 'Baño',
            'Jardín' => 'Jardín',
            'Electrónica' => 'Electrónica',
            'Experiencias' => 'Experiencias',
            'Luna de miel' => 'Luna de miel',
            'Fondos' => 'Fondos',
            'Otro' => 'Otro'
        ];
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
