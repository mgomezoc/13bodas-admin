<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContentModuleModel;
use App\Models\EventModel;

class ContentModules extends BaseController
{
    protected EventModel $eventModel;
    protected ContentModuleModel $moduleModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->moduleModel = new ContentModuleModel();
    }

    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        $modules = $this->moduleModel->getByEvent($eventId);

        return view('admin/modules/index', [
            'pageTitle' => 'Módulos: ' . $event['couple_title'],
            'event' => $event,
            'modules' => $modules,
            'moduleTypes' => ContentModuleModel::MODULE_TYPES,
        ]);
    }

    public function update(string $eventId, string $moduleId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $module = $this->moduleModel->find($moduleId);
        if (!$module || $module['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Módulo no encontrado.']);
        }

        $rules = [
            'css_id' => 'permit_empty|max_length[100]',
            'sort_order' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $payload = trim((string) $this->request->getPost('content_payload'));
        if ($payload !== '') {
            json_decode($payload);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'content_payload debe ser JSON válido.',
                ]);
            }
        }

        $data = [
            'css_id' => $this->request->getPost('css_id'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_enabled' => $this->request->getPost('is_enabled') ? 1 : 0,
            'content_payload' => $payload !== '' ? $payload : null,
        ];

        $this->moduleModel->update($moduleId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Módulo actualizado correctamente.',
        ]);
    }

    public function reorder(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $order = $this->request->getPost('order');
        if (!is_array($order)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Orden inválido.',
            ]);
        }

        $updated = $this->moduleModel->updateOrder($eventId, $order);

        return $this->response->setJSON([
            'success' => $updated,
            'message' => $updated ? 'Orden actualizado.' : 'No se pudo actualizar el orden.',
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
