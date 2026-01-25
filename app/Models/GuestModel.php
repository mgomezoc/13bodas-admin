<?php

namespace App\Models;

use CodeIgniter\Model;

class GuestModel extends Model
{
    protected $table            = 'guests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'group_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'is_child',
        'is_primary_contact',
        'rsvp_status',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'first_name' => 'required|max_length[100]',
        'last_name'  => 'required|max_length[100]',
        'email'      => 'permit_empty|valid_email|max_length[255]',
    ];

    /**
     * Obtener invitados de un evento
     */
    public function getByEvent(string $eventId): array
    {
        return $this->select('guests.*, guest_groups.group_name, guest_groups.access_code')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guest_groups.event_id', $eventId)
            ->orderBy('guest_groups.group_name', 'ASC')
            ->orderBy('guests.is_primary_contact', 'DESC')
            ->orderBy('guests.first_name', 'ASC')
            ->findAll();
    }

    /**
     * Obtener invitados de un grupo
     */
    public function getByGroup(string $groupId): array
    {
        return $this->where('group_id', $groupId)
            ->orderBy('is_primary_contact', 'DESC')
            ->orderBy('first_name', 'ASC')
            ->findAll();
    }

    /**
     * Obtener invitado con su respuesta RSVP
     */
    public function getWithRsvp(string $guestId): ?array
    {
        $guest = $this->find($guestId);
        if (!$guest) {
            return null;
        }

        $rsvpModel = new RsvpResponseModel();
        $guest['rsvp'] = $rsvpModel->where('guest_id', $guestId)
            ->orderBy('created_at', 'DESC')
            ->first();
        
        return $guest;
    }

    /**
     * Crear invitado
     */
    public function createGuest(array $data): ?string
    {
        $guestId = UserModel::generateUUID();
        $data['id'] = $guestId;
        $data['rsvp_status'] = $data['rsvp_status'] ?? 'pending';
        $data['is_child'] = $data['is_child'] ?? 0;
        $data['is_primary_contact'] = $data['is_primary_contact'] ?? 0;

        if ($this->insert($data)) {
            return $guestId;
        }
        
        return null;
    }

    /**
     * Obtener estadísticas RSVP de un evento
     */
    public function getRsvpStatsByEvent(string $eventId): array
    {
        $db = \Config\Database::connect();
        
        $stats = $db->table('guests')
            ->select('guests.rsvp_status, COUNT(*) as count')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guest_groups.event_id', $eventId)
            ->groupBy('guests.rsvp_status')
            ->get()
            ->getResultArray();

        $result = [
            'pending' => 0,
            'accepted' => 0,
            'declined' => 0,
            'total' => 0
        ];

        foreach ($stats as $stat) {
            $result[$stat['rsvp_status']] = (int) $stat['count'];
            $result['total'] += (int) $stat['count'];
        }

        return $result;
    }

    /**
     * Buscar invitados
     */
    public function search(string $eventId, string $query): array
    {
        return $this->select('guests.*, guest_groups.group_name')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guest_groups.event_id', $eventId)
            ->groupStart()
                ->like('guests.first_name', $query)
                ->orLike('guests.last_name', $query)
                ->orLike('guests.email', $query)
                ->orLike('guest_groups.group_name', $query)
            ->groupEnd()
            ->findAll();
    }

    /**
     * Importar invitados desde array (para CSV/Excel)
     */
    public function importGuests(string $eventId, array $guestsData): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $groupModel = new GuestGroupModel();
        $imported = 0;
        $errors = [];

        foreach ($guestsData as $index => $row) {
            $groupName = $row['group_name'] ?? $row['first_name'] . ' ' . $row['last_name'];
            
            // Buscar o crear grupo
            $group = $groupModel->where('event_id', $eventId)
                ->where('group_name', $groupName)
                ->first();

            if (!$group) {
                $groupId = $groupModel->createGroup([
                    'event_id' => $eventId,
                    'group_name' => $groupName,
                    'max_additional_guests' => $row['max_plus_ones'] ?? 0
                ]);
            } else {
                $groupId = $group['id'];
            }

            // Crear invitado
            $guestId = $this->createGuest([
                'group_id' => $groupId,
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'] ?? null,
                'phone_number' => $row['phone'] ?? null,
                'is_child' => $row['is_child'] ?? 0,
                'is_primary_contact' => 1
            ]);

            if ($guestId) {
                $imported++;
            } else {
                $errors[] = "Fila " . ($index + 1) . ": Error al importar " . $row['first_name'] . " " . $row['last_name'];
            }
        }

        $db->transComplete();

        return [
            'success' => $db->transStatus(),
            'imported' => $imported,
            'errors' => $errors
        ];
    }
}
