<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\EventCustomDomain;
use App\Enums\DomainRequestStatus;
use CodeIgniter\Model;

class EventCustomDomainModel extends Model
{
    protected $table            = 'event_custom_domains';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = EventCustomDomain::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id',
        'event_id',
        'domain',
        'status',
        'requested_by_user_id',
        'admin_notes',
        'dns_configured_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findLatestByEvent(string $eventId): ?EventCustomDomain
    {
        $record = $this->where('event_id', $eventId)
            ->orderBy('created_at', 'DESC')
            ->first();

        return $record instanceof EventCustomDomain ? $record : null;
    }

    public function findActiveRequestByEvent(string $eventId): ?EventCustomDomain
    {
        $record = $this->where('event_id', $eventId)
            ->whereIn('status', [
                DomainRequestStatus::Requested->value,
                DomainRequestStatus::Processing->value,
            ])
            ->orderBy('created_at', 'DESC')
            ->first();

        return $record instanceof EventCustomDomain ? $record : null;
    }

    public function existsByDomain(string $domain): bool
    {
        return $this->where('domain', $domain)->countAllResults() > 0;
    }
}
