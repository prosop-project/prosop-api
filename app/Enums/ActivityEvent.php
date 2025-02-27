<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum for activity events
 */
enum ActivityEvent: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case LOGIN = 'login';
    case ACTIVITYLOG_CLEAN_COMMAND = 'activitylog:clean';
}
