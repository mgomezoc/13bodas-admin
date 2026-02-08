<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\TimelineItem;
use CodeIgniter\Model;

class TimelineItemModel extends Model
{
    protected $table            = 'event_timeline_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = TimelineItem::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id',
        'event_id',
        'year',
        'title',
        'description',
        'image_url',
        'sort_order',
        'created_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * @return TimelineItem[]
     */
    public function getByEvent(string $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
