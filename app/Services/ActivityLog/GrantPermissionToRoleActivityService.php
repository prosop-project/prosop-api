<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @class GrantPermissionToRoleActivityService
 */
final readonly class GrantPermissionToRoleActivityService
{
    /**
     * Log the activity.
     *
     * @param Role $role
     * @param Permission $permission
     *
     * @return void
     */
    public function log(Role $role, Permission $permission): void
    {
        activity(ActivityLogName::GRANT_PERMISSION_TO_ROLE_ACTIVITY->value)
            ->by(auth()->user())
            ->on($role)
            ->withProperties([
                'role' => $role->getAttributes(),
                'permission' => $permission->getAttributes(),
            ])
            ->event(ActivityEvent::CREATED->value)
            ->log('Permission is granted to the role!');
    }
}
