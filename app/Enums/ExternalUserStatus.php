<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum for external user status.
 */
enum ExternalUserStatus: string
{
    case ACTIVE = 'active';
    case UPDATING = 'updating';
    case CREATING = 'creating';
    case CREATED = 'created';

    /**
     * Normalize the received external status.
     *
     * @param string|null $status
     *
     * @return string|null
     */
    public static function normalize(?string $status = null): ?string
    {
        // If the status is null, return null.
        if (is_null($status)) {
            return null;
        }

        // Convert the status to lowercase.
        $lowercaseStatus = strtolower($status);

        // Try to get the enum value from the lowercase status.
        return self::tryFrom($lowercaseStatus)?->value ?? $lowercaseStatus;
    }
}
