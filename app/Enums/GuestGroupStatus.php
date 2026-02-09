<?php

declare(strict_types=1);

namespace App\Enums;

enum GuestGroupStatus: string
{
    case Invited = 'invited';
    case Viewed = 'viewed';
    case Partial = 'partial';
    case Responded = 'responded';
}
