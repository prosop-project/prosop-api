<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum for analysis operation name.
 */
enum AnalysisOperationName: string
{
    case SEARCH_USERS_BY_IMAGE = 'search_users_by_image';
}
