<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\TimelineItemModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Timeline extends BaseController
{
    public function __construct(
        private ?EventModel $eventModel = null,
        private ?TimelineItemModel $timelineModel = null
    ) {
        $this->eventModel = $eventModel ?? new EventModel();
        $this->timelineModel = $timelineModel ?? new TimelineItemModel();
    }

    public function index(string $eventId): string|ResponseInterface
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Evento no encontrado.');
        }

        $items = $this->timelineModel->getByEvent($eventId);
        $items = array_map(static fn ($item) => $item->toArray(), $items);

        return view('admin/timeline/index', [
            'pageTitle' => 'Historia: ' . $event['couple_title'],
            'event' => $event,
            'items' => $items,
        ]);
    }

    public function new(string $eventId): string|ResponseInterface
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Evento no encontrado.');
        }

        return view('admin/timeline/form', [
            'pageTitle' => 'Nuevo hito',
            'event' => $event,
            'item' => null,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function create(string $eventId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Sin acceso al evento.');
        }

        $rules = [
            'year' => 'required|max_length[20]',
            'title' => 'required|max_length[150]',
            'description' => 'permit_empty',
            'sort_order' => 'permit_empty|integer',
        ];

        $imageFile = $this->request->getFile('image_file');
        if ($imageFile && $imageFile->getSize() > 0) {
            $rules['image_file'] = 'uploaded[image_file]|is_image[image_file]|mime_in[image_file,image/jpg,image/jpeg,image/png,image/webp,image/gif]|max_size[image_file,10240]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id' => UserModel::generateUUID(),
            'event_id' => $eventId,
            'year' => $this->request->getPost('year'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?? 0),
        ];

        if ($imageFile && $imageFile->getSize() > 0 && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/events/' . $eventId . '/timeline/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $newName = $imageFile->getRandomName();
            $imageFile->move($uploadPath, $newName);
            $data['image_url'] = 'uploads/events/' . $eventId . '/timeline/' . $newName;
        }

        if ($this->timelineModel->insert($data)) {
            return redirect()->to(url_to('admin.timeline.index', $eventId))
                ->with('success', 'Hito creado correctamente.');
        }

        return redirect()->back()->withInput()->with('error', 'Error al crear el hito.');
    }

    public function edit(string $eventId, string $itemId): string|ResponseInterface
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Evento no encontrado.');
        }

        $item = $this->timelineModel->find($itemId);

        if (!$item || $item->event_id !== $eventId) {
            return redirect()->to(url_to('admin.timeline.index', $eventId))
                ->with('error', 'Hito no encontrado.');
        }

        return view('admin/timeline/form', [
            'pageTitle' => 'Editar hito',
            'event' => $event,
            'item' => $item->toArray(),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update(string $eventId, string $itemId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Sin acceso al evento.');
        }

        $item = $this->timelineModel->find($itemId);

        if (!$item || $item->event_id !== $eventId) {
            return redirect()->to(url_to('admin.timeline.index', $eventId))
                ->with('error', 'Hito no encontrado.');
        }

        $rules = [
            'year' => 'required|max_length[20]',
            'title' => 'required|max_length[150]',
            'description' => 'permit_empty',
            'sort_order' => 'permit_empty|integer',
        ];

        $imageFile = $this->request->getFile('image_file');
        if ($imageFile && $imageFile->getSize() > 0) {
            $rules['image_file'] = 'uploaded[image_file]|is_image[image_file]|mime_in[image_file,image/jpg,image/jpeg,image/png,image/webp,image/gif]|max_size[image_file,10240]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'year' => $this->request->getPost('year'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?? 0),
        ];

        if ($imageFile && $imageFile->getSize() > 0 && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/events/' . $eventId . '/timeline/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $newName = $imageFile->getRandomName();
            $imageFile->move($uploadPath, $newName);
            $data['image_url'] = 'uploads/events/' . $eventId . '/timeline/' . $newName;
        }

        if ($this->timelineModel->update($itemId, $data)) {
            return redirect()->to(url_to('admin.timeline.index', $eventId))
                ->with('success', 'Hito actualizado correctamente.');
        }

        return redirect()->back()->withInput()->with('error', 'Error al actualizar el hito.');
    }

    public function delete(string $eventId, string $itemId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sin acceso al evento.',
            ]);
        }

        $item = $this->timelineModel->find($itemId);

        if (!$item || $item->event_id !== $eventId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Hito no encontrado.',
            ]);
        }

        if ($this->timelineModel->delete($itemId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Hito eliminado correctamente.',
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'No se pudo eliminar el hito.',
        ]);
    }

    protected function canAccessEvent(string $eventId): bool
    {
        $session = session();
        $userRoles = $session->get('user_roles') ?? [];

        if (
            in_array('superadmin', $userRoles, true)
            || in_array('admin', $userRoles, true)
            || in_array('staff', $userRoles, true)
        ) {
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
