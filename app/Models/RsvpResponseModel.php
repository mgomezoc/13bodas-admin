<?php

namespace App\Models;

use CodeIgniter\Model;

class RsvpResponseModel extends Model
{
    protected $table            = 'rsvp_responses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'guest_id',
        'attending_status',
        'meal_option_id',
        'dietary_restrictions',
        'transportation_requested',
        'song_request',
        'message_to_couple',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Crear o actualizar respuesta RSVP
     */
    public function saveResponse(array $data): ?string
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Verificar si ya existe una respuesta
        $existing = $this->where('guest_id', $data['guest_id'])->first();

        if ($existing) {
            // Actualizar existente
            $this->update($existing['id'], $data);
            $responseId = $existing['id'];
        } else {
            // Crear nueva
            $responseId = UserModel::generateUUID();
            $data['id'] = $responseId;
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->insert($data);
        }

        // Actualizar estado del invitado
        $guestModel = new GuestModel();
        $guestModel->update($data['guest_id'], [
            'rsvp_status' => $data['attending_status']
        ]);

        // Actualizar estado del grupo si todos han respondido
        $guest = $guestModel->find($data['guest_id']);
        if ($guest) {
            $this->updateGroupStatus($guest['group_id']);
        }

        $db->transComplete();

        return $db->transStatus() ? $responseId : null;
    }

    /**
     * Actualizar estado del grupo basado en respuestas
     */
    protected function updateGroupStatus(string $groupId): void
    {
        $guestModel = new GuestModel();
        $groupModel = new GuestGroupModel();

        $guests = $guestModel->where('group_id', $groupId)->findAll();
        $totalGuests = count($guests);
        $respondedGuests = 0;

        foreach ($guests as $guest) {
            if ($guest['rsvp_status'] !== 'pending') {
                $respondedGuests++;
            }
        }

        if ($respondedGuests === 0) {
            $status = 'viewed';
        } elseif ($respondedGuests < $totalGuests) {
            $status = 'partial';
        } else {
            $status = 'responded';
        }

        $groupModel->update($groupId, [
            'current_status' => $status,
            'responded_at' => $status === 'responded' ? date('Y-m-d H:i:s') : null
        ]);
    }

    /**
     * Obtener respuestas de un evento
     */
    public function getByEvent(string $eventId): array
    {
        return $this->select('rsvp_responses.*, guests.first_name, guests.last_name, guests.email, guest_groups.group_name, menu_options.name as meal_name')
            ->join('guests', 'guests.id = rsvp_responses.guest_id')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->join('menu_options', 'menu_options.id = rsvp_responses.meal_option_id', 'left')
            ->where('guest_groups.event_id', $eventId)
            ->orderBy('rsvp_responses.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Obtener resumen de opciones de menú
     */
    public function getMealSummary(string $eventId): array
    {
        $db = \Config\Database::connect();
        
        return $db->table('rsvp_responses')
            ->select('menu_options.name, COUNT(*) as count')
            ->join('guests', 'guests.id = rsvp_responses.guest_id')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->join('menu_options', 'menu_options.id = rsvp_responses.meal_option_id')
            ->where('guest_groups.event_id', $eventId)
            ->where('rsvp_responses.attending_status', 'accepted')
            ->groupBy('menu_options.id')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener listado de restricciones dietéticas
     */
    public function getDietaryRestrictions(string $eventId): array
    {
        return $this->select('guests.first_name, guests.last_name, rsvp_responses.dietary_restrictions')
            ->join('guests', 'guests.id = rsvp_responses.guest_id')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guest_groups.event_id', $eventId)
            ->where('rsvp_responses.dietary_restrictions IS NOT NULL')
            ->where('rsvp_responses.dietary_restrictions !=', '')
            ->findAll();
    }

    /**
     * Obtener solicitudes de transporte
     */
    public function getTransportRequests(string $eventId): array
    {
        return $this->select('guests.first_name, guests.last_name, guests.email, guest_groups.group_name')
            ->join('guests', 'guests.id = rsvp_responses.guest_id')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guest_groups.event_id', $eventId)
            ->where('rsvp_responses.transportation_requested', 1)
            ->findAll();
    }

    /**
     * Obtener solicitudes de canciones
     */
    public function getSongRequests(string $eventId): array
    {
        return $this->select('guests.first_name, guests.last_name, rsvp_responses.song_request')
            ->join('guests', 'guests.id = rsvp_responses.guest_id')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guest_groups.event_id', $eventId)
            ->where('rsvp_responses.song_request IS NOT NULL')
            ->where('rsvp_responses.song_request !=', '')
            ->findAll();
    }
}
