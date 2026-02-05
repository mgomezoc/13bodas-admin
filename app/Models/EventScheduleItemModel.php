<?php

namespace App\Models;

use CodeIgniter\Model;

class EventScheduleItemModel extends Model
{
    protected $table            = 'event_schedule_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id',
        'event_id',
        'location_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'icon',
        'sort_order',
        'is_visible',
    ];

    protected $useTimestamps = false;

    public function getByEvent(string $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('starts_at', 'ASC')
            ->findAll();
    }

    public function createItem(array $data): ?string
    {
        $itemId = UserModel::generateUUID();
        $data['id'] = $itemId;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_visible'] = $data['is_visible'] ?? 1;

        if ($this->insert($data)) {
            return $itemId;
        }

        return null;
    }
}
