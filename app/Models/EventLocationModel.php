<?php

namespace App\Models;

use CodeIgniter\Model;

class EventLocationModel extends Model
{
    protected $table            = 'event_locations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id',
        'event_id',
        'code',
        'name',
        'address',
        'geo_lat',
        'geo_lng',
        'maps_url',
        'waze_url',
        'notes',
        'sort_order',
    ];

    protected $useTimestamps = false;

    public function getByEvent(string $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function createLocation(array $data): ?string
    {
        $locationId = UserModel::generateUUID();
        $data['id'] = $locationId;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($this->insert($data)) {
            return $locationId;
        }

        return null;
    }
}
