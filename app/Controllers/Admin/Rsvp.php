<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\GuestModel;
use App\Models\GuestGroupModel;
use App\Models\RsvpResponseModel;
use App\Models\MenuOptionModel;

class Rsvp extends BaseController
{
    protected $eventModel;
    protected $guestModel;
    protected $groupModel;
    protected $rsvpModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->guestModel = new GuestModel();
        $this->groupModel = new GuestGroupModel();
        $this->rsvpModel = new RsvpResponseModel();
    }

    /**
     * Dashboard de RSVPs del evento
     */
    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Evento no encontrado.');
        }

        // Estadísticas generales
        $stats = $this->guestModel->getRsvpStatsByEvent($eventId);
        
        // Resumen de menú
        $mealSummary = $this->rsvpModel->getMealSummary($eventId);
        
        // Restricciones dietéticas
        $dietaryRestrictions = $this->rsvpModel->getDietaryRestrictions($eventId);
        
        // Solicitudes de transporte
        $transportRequests = $this->rsvpModel->getTransportRequests($eventId);
        
        // Solicitudes de canciones
        $songRequests = $this->rsvpModel->getSongRequests($eventId);
        
        // Mensajes de los invitados
        $messages = $this->rsvpModel->getMessages($eventId);

        $data = [
            'pageTitle' => 'Confirmaciones: ' . $event['couple_title'],
            'event' => $event,
            'stats' => $stats,
            'mealSummary' => $mealSummary,
            'dietaryRestrictions' => $dietaryRestrictions,
            'transportRequests' => $transportRequests,
            'songRequests' => $songRequests,
            'messages' => $messages
        ];

        return view('admin/rsvp/index', $data);
    }

    /**
     * API: Lista de respuestas RSVP para Bootstrap Table
     */
    public function list(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['total' => 0, 'rows' => []]);
        }

        $filter = $this->request->getGet('rsvp_status');
        
        $builder = $this->guestModel
            ->select('guests.*, guest_groups.group_name, guest_groups.access_code,
                      rsvp_responses.attending_status, rsvp_responses.meal_option_id,
                      rsvp_responses.dietary_restrictions, rsvp_responses.message_to_couple,
                      rsvp_responses.responded_at, menu_options.name as meal_name')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->join('rsvp_responses', 'rsvp_responses.guest_id = guests.id', 'left')
            ->join('menu_options', 'menu_options.id = rsvp_responses.meal_option_id', 'left')
            ->where('guest_groups.event_id', $eventId);

        if ($filter && in_array($filter, ['pending', 'accepted', 'declined'])) {
            $builder->where('guests.rsvp_status', $filter);
        }

        $guests = $builder->orderBy('guest_groups.group_name', 'ASC')
                          ->orderBy('guests.is_primary_contact', 'DESC')
                          ->findAll();

        return $this->response->setJSON([
            'total' => count($guests),
            'rows' => $guests
        ]);
    }

    /**
     * Exportar RSVPs a CSV
     */
    public function export(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $event = $this->eventModel->find($eventId);
        
        $guests = $this->guestModel
            ->select('guests.*, guest_groups.group_name, guest_groups.access_code,
                      rsvp_responses.attending_status, rsvp_responses.meal_option_id,
                      rsvp_responses.dietary_restrictions, rsvp_responses.transportation_requested,
                      rsvp_responses.song_request, rsvp_responses.message_to_couple,
                      rsvp_responses.responded_at, menu_options.name as meal_name')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->join('rsvp_responses', 'rsvp_responses.guest_id = guests.id', 'left')
            ->join('menu_options', 'menu_options.id = rsvp_responses.meal_option_id', 'left')
            ->where('guest_groups.event_id', $eventId)
            ->orderBy('guest_groups.group_name', 'ASC')
            ->findAll();

        $filename = 'rsvp-' . $event['slug'] . '-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados
        fputcsv($output, [
            'Grupo', 'Código', 'Nombre', 'Apellido', 'Email', 'Teléfono',
            'Estado RSVP', 'Opción Menú', 'Restricciones', 'Transporte',
            'Canción', 'Mensaje', 'Fecha Respuesta', 'Es Niño'
        ]);
        
        // Datos
        foreach ($guests as $guest) {
            $rsvpStatus = match($guest['rsvp_status']) {
                'accepted' => 'Confirmado',
                'declined' => 'No Asiste',
                default => 'Pendiente'
            };
            
            fputcsv($output, [
                $guest['group_name'],
                $guest['access_code'],
                $guest['first_name'],
                $guest['last_name'],
                $guest['email'] ?? '',
                $guest['phone_number'] ?? '',
                $rsvpStatus,
                $guest['meal_name'] ?? '',
                $guest['dietary_restrictions'] ?? '',
                $guest['transportation_requested'] ? 'Sí' : 'No',
                $guest['song_request'] ?? '',
                $guest['message_to_couple'] ?? '',
                $guest['responded_at'] ? date('d/m/Y H:i', strtotime($guest['responded_at'])) : '',
                $guest['is_child'] ? 'Sí' : 'No'
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Exportar resumen de menú
     */
    public function exportMeals(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $event = $this->eventModel->find($eventId);
        $mealSummary = $this->rsvpModel->getMealSummary($eventId);
        $dietaryRestrictions = $this->rsvpModel->getDietaryRestrictions($eventId);

        $filename = 'menu-resumen-' . $event['slug'] . '-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Resumen de opciones de menú
        fputcsv($output, ['=== RESUMEN DE MENÚ ===']);
        fputcsv($output, ['Opción', 'Cantidad']);
        
        foreach ($mealSummary as $meal) {
            fputcsv($output, [$meal['name'], $meal['count']]);
        }
        
        fputcsv($output, []);
        fputcsv($output, ['=== RESTRICCIONES DIETÉTICAS ===']);
        fputcsv($output, ['Invitado', 'Restricción']);
        
        foreach ($dietaryRestrictions as $restriction) {
            fputcsv($output, [
                $restriction['first_name'] . ' ' . $restriction['last_name'],
                $restriction['dietary_restrictions']
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Exportar lista de canciones
     */
    public function exportSongs(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'));
        }

        $event = $this->eventModel->find($eventId);
        $songRequests = $this->rsvpModel->getSongRequests($eventId);

        $filename = 'canciones-' . $event['slug'] . '-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['Canción Sugerida', 'Sugerido Por']);
        
        foreach ($songRequests as $song) {
            fputcsv($output, [
                $song['song_request'],
                $song['first_name'] . ' ' . $song['last_name']
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Cambiar estado RSVP manualmente
     */
    public function updateStatus(string $eventId, string $guestId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $guest = $this->guestModel->find($guestId);
        if (!$guest) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invitado no encontrado.']);
        }

        $newStatus = $this->request->getPost('status');
        if (!in_array($newStatus, ['pending', 'accepted', 'declined'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Estado inválido.']);
        }

        $this->guestModel->update($guestId, ['rsvp_status' => $newStatus]);

        // Si se confirma o rechaza, crear/actualizar respuesta RSVP
        if ($newStatus !== 'pending') {
            $existingResponse = $this->rsvpModel->where('guest_id', $guestId)->first();
            
            $responseData = [
                'guest_id' => $guestId,
                'attending_status' => $newStatus === 'accepted' ? 'yes' : 'no',
                'responded_at' => date('Y-m-d H:i:s'),
                'response_method' => 'admin_manual'
            ];

            if ($existingResponse) {
                $this->rsvpModel->update($existingResponse['id'], $responseData);
            } else {
                $this->rsvpModel->insert($responseData);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Estado actualizado correctamente.'
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
