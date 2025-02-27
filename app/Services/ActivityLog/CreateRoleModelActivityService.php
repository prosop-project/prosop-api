<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use Spatie\Permission\Models\Role;

/**
 * @class CreateRoleModelActivityService
 */
final readonly class CreateRoleModelActivityService
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
                'attributes' => $role->getAttributes(),
            ])
            ->event(ActivityEvent::CREATED->value)
            ->log('Role is created!');
    }
}
