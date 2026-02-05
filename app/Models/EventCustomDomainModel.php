<?php

namespace App\Models;

use CodeIgniter\Model;

class EventCustomDomainModel extends Model
{
    protected $table            = 'event_custom_domains';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id',
        'event_id',
        'domain',
        'status',
    ];

    protected $useTimestamps = false;

    public function getByEvent(string $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('domain', 'ASC')
            ->findAll();
    }

    public function createDomain(array $data): ?string
    {
        $domainId = UserModel::generateUUID();
        $data['id'] = $domainId;

        if ($this->insert($data)) {
            return $domainId;
        }

        return null;
    }
}
