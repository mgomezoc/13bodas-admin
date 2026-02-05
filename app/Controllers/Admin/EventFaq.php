<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventFaqItemModel;
use App\Models\EventModel;

class EventFaq extends BaseController
{
    protected EventModel $eventModel;
    protected EventFaqItemModel $faqModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->faqModel = new EventFaqItemModel();
    }

    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        $items = $this->faqModel->getByEvent($eventId);

        return view('admin/faq/index', [
            'pageTitle' => 'FAQ: ' . $event['couple_title'],
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
            'question' => 'required|max_length[255]',
            'answer' => 'required',
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
            'question' => $this->request->getPost('question'),
            'answer' => $this->request->getPost('answer'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_visible' => $this->request->getPost('is_visible') ? 1 : 0,
        ];

        $itemId = $this->faqModel->createItem($data);

        if ($itemId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pregunta creada correctamente.',
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'No se pudo crear la pregunta.',
        ]);
    }

    public function update(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->faqModel->find($itemId);
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pregunta no encontrada.']);
        }

        $rules = [
            'question' => 'required|max_length[255]',
            'answer' => 'required',
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
            'question' => $this->request->getPost('question'),
            'answer' => $this->request->getPost('answer'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_visible' => $this->request->getPost('is_visible') ? 1 : 0,
        ];

        $this->faqModel->update($itemId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pregunta actualizada correctamente.',
        ]);
    }

    public function delete(string $eventId, string $itemId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $item = $this->faqModel->find($itemId);
        if (!$item || $item['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pregunta no encontrada.']);
        }

        $this->faqModel->delete($itemId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pregunta eliminada correctamente.',
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
