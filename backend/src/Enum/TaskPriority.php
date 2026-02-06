<?php

namespace App\Enum;

enum TaskPriority: string
{
    case LOW = 'Basse';
    case MEDIUM = 'Moyenne';
    case HIGH = 'Haute';

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
