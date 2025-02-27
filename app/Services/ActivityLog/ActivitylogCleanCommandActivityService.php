<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;

/**
 * @class ActivitylogCleanCommandActivityService
 */
final readonly class ActivitylogCleanCommandActivityService
{
    /**
     * Log the activity.
     *
     * @param mixed $days
     * @param mixed $logName
     *
     * @return void
     */
    public function log(mixed $days, mixed $logName): void
    {
        activity(ActivityLogName::ACTIVITYLOG_CLEAN_COMMAND_ACTIVITY->value)
            ->by(auth()->user())
            ->withProperties(array_filter([
                'days' => (int) $days,
                'log_name' => $logName,
            ], fn($value) => ! is_null($value)))
            ->event(ActivityEvent::ACTIVITYLOG_CLEAN_COMMAND->value)
            ->log('Activity log is cleaned!');
    }
}
