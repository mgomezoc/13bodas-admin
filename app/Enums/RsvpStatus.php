<?php

declare(strict_types=1);

namespace App\Enums;

enum RsvpStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
}
