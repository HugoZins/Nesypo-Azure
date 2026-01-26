<?php

namespace App\Enum;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public const VALUES = [
        self::LOW->value,
        self::MEDIUM->value,
        self::HIGH->value,
    ];
}



