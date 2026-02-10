<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class PaymentSetting extends Entity
{
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
