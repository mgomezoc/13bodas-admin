<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\GuestInvitationService;
use App\Models\EventModel;
use App\Models\GuestModel;
use App\Models\GuestGroupModel;
use CodeIgniter\HTTP\ResponseInterface;

class Guests extends BaseController
{
    public function __construct(
        protected EventModel $eventModel = new EventModel(),
        protected GuestModel $guestModel = new GuestModel(),
        protected GuestGroupModel $groupModel = new GuestGroupModel(),
        protected GuestInvitationService $invitationService = new GuestInvitationService()
    ) {
    }

    /**
     * Lista de invitados de un evento
     */
    public function index(string $eventId): ResponseInterface|string
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Evento no encontrado.');
        }

        $groups = $this->groupModel->getByEventWithGuestCount($eventId);
        $stats = $this->guestModel->getRsvpStatsByEvent($eventId);

        $data = [
            'pageTitle' => 'Invitados: ' . $event['couple_title'],
            'event' => $event,
            'groups' => $groups,
            'stats' => $stats
        ];

        return view('admin/guests/index', $data);
    }

    /**
     * API: Lista de invitados para Bootstrap Table
     */
    public function list(string $eventId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['total' => 0, 'rows' => []]);
        }

        $guests = $this->guestModel->getByEvent($eventId);

        return $this->response->setJSON([
            'total' => count($guests),
            'rows' => $guests
        ]);
    }

    /**
     * Formulario para crear invitado
     */
    public function create(string $eventId): ResponseInterface|string
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $groups = $this->groupModel->where('event_id', $eventId)->findAll();

        $data = [
            'pageTitle' => 'Nuevo Invitado',
            'event' => $event,
            'groups' => $groups
        ];

        return view('admin/guests/create', $data);
    }

    /**
     * Guardar nuevo invitado
     */
    public function store(string $eventId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $rules = [
            'first_name' => 'required|max_length[100]',
            'last_name' => 'required|max_length[100]',
            'email' => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Crear o obtener grupo
        $groupId = $this->request->getPost('group_id');
        
        if (empty($groupId) || $groupId === 'new') {
            // Crear nuevo grupo
            $groupName = $this->request->getPost('new_group_name') 
                ?: $this->request->getPost('first_name') . ' ' . $this->request->getPost('last_name');
            
            $groupId = $this->groupModel->createGroup([
                'event_id' => $eventId,
                'group_name' => $groupName,
                'max_additional_guests' => $this->request->getPost('max_additional_guests') ?: 0
            ]);
        }

        // Crear invitado
        $guestData = [
            'group_id' => $groupId,
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone_number' => $this->request->getPost('phone_number'),
            'is_child' => $this->request->getPost('is_child') ? 1 : 0,
            'is_primary_contact' => $this->request->getPost('is_primary_contact') ? 1 : 0,
        ];

        $guestId = $this->guestModel->createGuest($guestData);

        if ($guestId) {
            return redirect()->to(base_url('admin/events/' . $eventId . '/guests'))
                ->with('success', 'Invitado agregado correctamente.');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Error al agregar el invitado.');
    }

    /**
     * Formulario para editar invitado
     */
    public function edit(string $eventId, string $guestId): ResponseInterface|string
    {
        $event = $this->eventModel->find($eventId);
        $guest = $this->guestModel->find($guestId);
        
        if (!$event || !$guest || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $groups = $this->groupModel->where('event_id', $eventId)->findAll();

        $data = [
            'pageTitle' => 'Editar Invitado',
            'event' => $event,
            'guest' => $guest,
            'groups' => $groups
        ];

        return view('admin/guests/edit', $data);
    }

    /**
     * Actualizar invitado
     */
    public function update(string $eventId, string $guestId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $guest = $this->guestModel->find($guestId);
        if (!$guest) {
            return redirect()->to(base_url('admin/events/' . $eventId . '/guests'))
                ->with('error', 'Invitado no encontrado.');
        }

        $rules = [
            'first_name' => 'required|max_length[100]',
            'last_name' => 'required|max_length[100]',
            'email' => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $guestData = [
            'group_id' => $this->request->getPost('group_id'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone_number' => $this->request->getPost('phone_number'),
            'is_child' => $this->request->getPost('is_child') ? 1 : 0,
            'is_primary_contact' => $this->request->getPost('is_primary_contact') ? 1 : 0,
        ];

        $this->guestModel->update($guestId, $guestData);

        return redirect()->to(base_url('admin/events/' . $eventId . '/guests'))
            ->with('success', 'Invitado actualizado correctamente.');
    }

    /**
     * Eliminar invitado
     */
    public function delete(string $eventId, string $guestId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $guest = $this->guestModel->find($guestId);
        if (!$guest) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invitado no encontrado.']);
        }

        $this->guestModel->delete($guestId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Invitado eliminado correctamente.'
        ]);
    }

    /**
     * Formulario de importación
     */
    public function import(string $eventId): ResponseInterface|string
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $data = [
            'pageTitle' => 'Importar Invitados',
            'event' => $event
        ];

        return view('admin/guests/import', $data);
    }

    /**
     * Procesar importación de CSV
     */
    public function processImport(string $eventId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $file = $this->request->getFile('csv_file');
        
        if (!$file->isValid() || $file->getExtension() !== 'csv') {
            return redirect()->back()->with('error', 'Por favor sube un archivo CSV válido.');
        }

        $handle = fopen($file->getTempName(), 'r');
        $header = fgetcsv($handle); // Primera fila como encabezados
        
        $guestsData = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 2) {
                $guestsData[] = [
                    'first_name' => $row[0] ?? '',
                    'last_name' => $row[1] ?? '',
                    'email' => $row[2] ?? null,
                    'phone' => $row[3] ?? null,
                    'group_name' => $row[4] ?? null,
                    'is_child' => isset($row[5]) && strtolower($row[5]) === 'si' ? 1 : 0,
                ];
            }
        }
        fclose($handle);

        if (empty($guestsData)) {
            return redirect()->back()->with('error', 'El archivo CSV está vacío o no tiene el formato correcto.');
        }

        $result = $this->guestModel->importGuests($eventId, $guestsData);

        if ($result['success']) {
            $message = "Se importaron {$result['imported']} invitados correctamente.";
            if (!empty($result['errors'])) {
                $message .= " Errores: " . count($result['errors']);
            }
            return redirect()->to(base_url('admin/events/' . $eventId . '/guests'))
                ->with('success', $message);
        }

        return redirect()->back()->with('error', 'Error al importar invitados.');
    }

    /**
     * Exportar invitados a CSV
     */
    public function export(string $eventId): ResponseInterface|void
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $event = $this->eventModel->find($eventId);
        $guests = $this->guestModel->getByEvent($eventId);

        $filename = 'invitados-' . $event['slug'] . '-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados
        fputcsv($output, ['Nombre', 'Apellido', 'Email', 'Teléfono', 'Grupo', 'Estado RSVP', 'Es Niño']);
        
        // Datos
        foreach ($guests as $guest) {
            fputcsv($output, [
                $guest['first_name'],
                $guest['last_name'],
                $guest['email'] ?? '',
                $guest['phone_number'] ?? '',
                $guest['group_name'] ?? '',
                $guest['rsvp_status'],
                $guest['is_child'] ? 'Sí' : 'No'
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function inviteLink(string $eventId, string $guestId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return $this->response->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => 'Evento no encontrado.']);
        }

        $context = $this->invitationService->resolveContextForEvent($event, $guestId);
        if (!($context['success'] ?? false)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => $context['message'] ?? 'Invitado no encontrado.']);
        }

        $guest = $context['guest'] ?? [];

        return $this->response->setJSON([
            'success' => true,
            'invite_url' => $context['invite_url'],
            'guest' => [
                'id' => $guest['id'] ?? null,
                'first_name' => $guest['first_name'] ?? null,
                'last_name' => $guest['last_name'] ?? null,
                'email' => $guest['email'] ?? null,
                'phone_number' => $guest['phone_number'] ?? null,
            ],
            'event' => [
                'id' => $event['id'] ?? null,
                'couple_title' => $event['couple_title'] ?? null,
                'slug' => $event['slug'] ?? null,
                'access_mode' => $event['access_mode'] ?? null,
            ],
        ]);
    }

    public function sendInvite(string $eventId, string $guestId): ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $result = $this->invitationService->sendInvitation($eventId, $guestId);
        $status = ($result['success'] ?? false) ? 200 : 400;

        return $this->response->setStatusCode($status)->setJSON($result);
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
