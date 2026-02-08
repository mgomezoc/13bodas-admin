<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TimelineItem extends Entity
{
    protected $attributes = [
        'id' => null,
        'event_id' => null,
        'year' => null,
        'title' => null,
        'description' => null,
        'image_url' => null,
        'sort_order' => 0,
        'created_at' => null,
    ];
}
