<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use Spatie\Permission\Models\Role;

/**
 * @class DeleteRoleModelActivityService
 */
final readonly class DeleteRoleModelActivityService
{
    /**
     * Log the activity.
     *
     * @param Role $role
     *
     * @return void
     */
    public function log(Role $role): void
    {
        activity(ActivityLogName::ROLE_MODEL_ACTIVITY->value)
            ->by(auth()->user())
            ->on($role)
            ->withProperties([
                'old' => $role->getAttributes(),
            ])
            ->event(ActivityEvent::DELETED->value)
            ->log('Role is deleted!');
    }
}
