<?php

namespace App\Models;

use CodeIgniter\Model;

class EventFaqItemModel extends Model
{
    protected $table            = 'event_faq_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id',
        'event_id',
        'question',
        'answer',
        'sort_order',
        'is_visible',
    ];

    protected $useTimestamps = false;

    public function getByEvent(string $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('sort_order', 'ASC')
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
