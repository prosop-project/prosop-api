<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Http\Requests\Permission\AssignOrRemoveRoleRequest;
use App\Models\User;

/**
 * @class RemoveRoleFromUserActivityService
 */
final readonly class RemoveRoleFromUserActivityService
{
    /**
     * Log the activity.
     *
     * @param AssignOrRemoveRoleRequest $request
     * @param User $user
     *
     * @return void
     */
    public function log(AssignOrRemoveRoleRequest $request, User $user): void
    {
        activity(ActivityLogName::REMOVE_ROLE_FROM_USER_ACTIVITY->value)
            ->by(auth()->user())
            ->on($user)
            ->withProperties([
                'role' => $request->validated('role'),
                'user' => collect($user->getAttributes())->except(['password']),
            ])
            ->event(ActivityEvent::DELETED->value)
            ->log('Role removed from the user!');
    }
}
