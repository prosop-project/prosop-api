<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Http\Requests\Permission\AssignOrRemoveRoleRequest;
use App\Models\User;

/**
 * @class AssignRoleToUserActivityService
 */
final readonly class AssignRoleToUserActivityService
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
        activity(ActivityLogName::ASSIGN_ROLE_TO_USER_ACTIVITY->value)
            ->by(auth()->user())
            ->on($user)
            ->withProperties([
                'role' => $request->validated('role'),
                'user' => collect($user->getAttributes())->except(['password']),
            ])
            ->event(ActivityEvent::CREATED->value)
            ->log('Role assigned to the user!');
    }
}
