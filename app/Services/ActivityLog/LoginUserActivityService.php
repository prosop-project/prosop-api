<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;

/**
 * @class LoginUserActivityService
 */
final readonly class LoginUserActivityService
{
    /**
     * Log the activity.
     *
     * @param mixed $user
     *
     * @return void
     */
    public function log(mixed $user): void
    {
        activity(ActivityLogName::LOGIN_USER_ACTIVITY->value)
            ->by(auth()->user())
            ->withProperties([
                'user' => collect($user->getAttributes())->except(['password']),
            ])
            ->event(ActivityEvent::LOGIN->value)
            ->log('User is logged in!');
    }
}
