<?php

namespace App\Models;

use CodeIgniter\Model;

class GuestGroupModel extends Model
{
    protected $table            = 'guest_groups';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'event_id',
        'group_name',
        'access_code',
        'max_additional_guests',
        'is_vip',
        'current_status',
        'invited_at',
        'first_viewed_at',
        'last_viewed_at',
        'responded_at',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Generar código de acceso único
     */
    public static function generateAccessCode(int $length = 8): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $code;
    }

    /**
     * Obtener grupo por código de acceso
     */
    public function findByAccessCode(string $code, string $eventId): ?array
    {
        return $this->where('access_code', $code)
            ->where('event_id', $eventId)
            ->first();
    }

    /**
     * Obtener grupos de un evento con conteo de invitados
     */
    public function getByEventWithGuestCount(string $eventId): array
    {
        return $this->select('guest_groups.*, COUNT(guests.id) as guest_count')
            ->join('guests', 'guests.group_id = guest_groups.id', 'left')
            ->where('guest_groups.event_id', $eventId)
            ->groupBy('guest_groups.id')
            ->orderBy('guest_groups.group_name', 'ASC')
            ->findAll();
    }

    /**
     * Obtener grupo con sus invitados
     */
    public function getWithGuests(string $groupId): ?array
    {
        $group = $this->find($groupId);
        if (!$group) {
            return null;
        }

        $guestModel = new GuestModel();
        $group['guests'] = $guestModel->where('group_id', $groupId)->findAll();
        
        return $group;
    }

    /**
     * Crear grupo con código único
     */
    public function createGroup(array $data): ?string
    {
        $groupId = UserModel::generateUUID();
        $data['id'] = $groupId;
        
        // Generar código de acceso único
        do {
            $accessCode = self::generateAccessCode();
            $exists = $this->where('access_code', $accessCode)
                ->where('event_id', $data['event_id'])
                ->countAllResults() > 0;
        } while ($exists);
        
        $data['access_code'] = $accessCode;
        $data['current_status'] = $data['current_status'] ?? 'invited';
        $data['max_additional_guests'] = $data['max_additional_guests'] ?? 0;
        $data['is_vip'] = $data['is_vip'] ?? 0;

        if ($this->insert($data)) {
            return $groupId;
        }
        
        return null;
    }

    /**
     * Marcar grupo como visto
     */
    public function markAsViewed(string $groupId): bool
    {
        $group = $this->find($groupId);
        if (!$group) {
            return false;
        }

        $updateData = ['last_viewed_at' => date('Y-m-d H:i:s')];
        
        if (empty($group['first_viewed_at'])) {
            $updateData['first_viewed_at'] = date('Y-m-d H:i:s');
            $updateData['current_status'] = 'viewed';
        }

        return $this->update($groupId, $updateData);
    }

    /**
     * Obtener estadísticas por estado
     */
    public function getStatsByEvent(string $eventId): array
    {
        $db = \Config\Database::connect();
        
        $stats = $db->table('guest_groups')
            ->select('current_status, COUNT(*) as count')
            ->where('event_id', $eventId)
            ->groupBy('current_status')
            ->get()
            ->getResultArray();

        $result = [
            'invited' => 0,
            'viewed' => 0,
            'partial' => 0,
            'responded' => 0,
            'total' => 0
        ];

        foreach ($stats as $stat) {
            $result[$stat['current_status']] = (int) $stat['count'];
            $result['total'] += (int) $stat['count'];
        }

        return $result;
    }
}
