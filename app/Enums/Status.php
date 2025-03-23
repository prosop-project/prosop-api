<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum for status.
 */
enum Status: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
}
