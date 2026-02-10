<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class EventPayment extends Entity
{
    protected $dates = ['paid_at', 'expires_at', 'webhook_received_at', 'created_at', 'updated_at'];
    protected $casts = [
        'amount' => 'float',
    ];
}
