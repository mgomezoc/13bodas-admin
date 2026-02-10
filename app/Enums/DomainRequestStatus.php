<?php

declare(strict_types=1);

namespace App\Enums;

enum DomainRequestStatus: string
{
    case Requested = 'requested';
    case Processing = 'processing';
    case Completed = 'completed';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Solicitado',
            self::Processing => 'En proceso',
            self::Completed => 'Completado',
        };
    }
}
