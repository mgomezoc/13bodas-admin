<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class EventCustomDomain extends Entity
{
    protected $attributes = [
        'id' => null,
        'event_id' => null,
        'domain' => null,
        'status' => null,
        'requested_by_user_id' => null,
        'admin_notes' => null,
        'dns_configured_at' => null,
        'created_at' => null,
        'updated_at' => null,
    ];
}
