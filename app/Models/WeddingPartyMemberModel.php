<?php

namespace App\Models;

use CodeIgniter\Model;

class WeddingPartyMemberModel extends Model
{
    protected $table            = 'wedding_party_members';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'event_id',
        'full_name',
        'role',
        'category',
        'bio',
        'image_url',
        'social_links',
        'display_order',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    // Categorías disponibles
    const CATEGORIES = [
        'bride_side' => 'Lado de la Novia',
        'groom_side' => 'Lado del Novio',
        'officiant' => 'Oficiante',
        'other' => 'Otro'
    ];

    /**
     * Obtener miembros de un evento
     */
    public function getByEvent(string $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('category', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->findAll();
    }

    /**
     * Obtener miembros por categoría
     */
    public function getByCategory(string $eventId, string $category): array
    {
        return $this->where('event_id', $eventId)
            ->where('category', $category)
            ->orderBy('display_order', 'ASC')
            ->findAll();
    }

    /**
     * Crear miembro
     */
    public function createMember(array $data): ?string
    {
        $memberId = UserModel::generateUUID();
        $data['id'] = $memberId;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Obtener siguiente orden para la categoría
        if (!isset($data['display_order'])) {
            $maxOrder = $this->where('event_id', $data['event_id'])
                ->where('category', $data['category'])
                ->selectMax('display_order')
                ->first();
            $data['display_order'] = ($maxOrder['display_order'] ?? 0) + 1;
        }

        // Convertir social_links a JSON si es array
        if (isset($data['social_links']) && is_array($data['social_links'])) {
            $data['social_links'] = json_encode($data['social_links']);
        }

        if ($this->insert($data)) {
            return $memberId;
        }
        
        return null;
    }

    /**
     * Actualizar orden de miembros
     */
    public function updateOrder(string $eventId, string $category, array $memberIds): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($memberIds as $index => $memberId) {
            $this->update($memberId, ['display_order' => $index + 1]);
        }

        $db->transComplete();
        return $db->transStatus();
    }
}
