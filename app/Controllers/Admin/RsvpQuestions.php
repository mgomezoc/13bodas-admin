<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\RsvpQuestionModel;

class RsvpQuestions extends BaseController
{
    protected EventModel $eventModel;
    protected RsvpQuestionModel $questionModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->questionModel = new RsvpQuestionModel();
    }

    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        $questions = $this->questionModel->getByEvent($eventId);

        return view('admin/rsvp_questions/index', [
            'pageTitle' => 'Preguntas RSVP: ' . $event['couple_title'],
            'event' => $event,
            'questions' => $questions,
        ]);
    }

    public function store(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $rules = [
            'code' => 'required|max_length[80]',
            'label' => 'required|max_length[255]',
            'type' => 'required|in_list[text,textarea,select,checkbox,radio,number]',
            'options_json' => 'permit_empty',
            'sort_order' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $optionsJson = trim((string) $this->request->getPost('options_json'));
        if ($optionsJson !== '') {
            json_decode($optionsJson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'options_json debe ser JSON válido.',
                ]);
            }
        }

        $data = [
            'event_id' => $eventId,
            'code' => $this->request->getPost('code'),
            'label' => $this->request->getPost('label'),
            'type' => $this->request->getPost('type'),
            'options_json' => $optionsJson !== '' ? $optionsJson : null,
            'is_required' => $this->request->getPost('is_required') ? 1 : 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
        ];

        $questionId = $this->questionModel->createQuestion($data);

        if ($questionId) {
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

    public function update(string $eventId, string $questionId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $question = $this->questionModel->find($questionId);
        if (!$question || $question['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pregunta no encontrada.']);
        }

        $rules = [
            'code' => 'required|max_length[80]',
            'label' => 'required|max_length[255]',
            'type' => 'required|in_list[text,textarea,select,checkbox,radio,number]',
            'options_json' => 'permit_empty',
            'sort_order' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $optionsJson = trim((string) $this->request->getPost('options_json'));
        if ($optionsJson !== '') {
            json_decode($optionsJson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'options_json debe ser JSON válido.',
                ]);
            }
        }

        $data = [
            'code' => $this->request->getPost('code'),
            'label' => $this->request->getPost('label'),
            'type' => $this->request->getPost('type'),
            'options_json' => $optionsJson !== '' ? $optionsJson : null,
            'is_required' => $this->request->getPost('is_required') ? 1 : 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
        ];

        $this->questionModel->update($questionId, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pregunta actualizada correctamente.',
        ]);
    }

    public function delete(string $eventId, string $questionId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $question = $this->questionModel->find($questionId);
        if (!$question || $question['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pregunta no encontrada.']);
        }

        $this->questionModel->delete($questionId);

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
