<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use Spatie\Permission\Models\Role;

/**
 * @class UpdateRoleModelActivityService
 */
final readonly class UpdateRoleModelActivityService
{
    /**
     * Log the activity.
     *
     * @param Role $role
     * @param mixed $oldAttributes
     * @param mixed $changedAttributes
     *
     * @return void
     */
    public function log(Role $role, mixed $oldAttributes, mixed $changedAttributes): void
    {
        activity(ActivityLogName::ROLE_MODEL_ACTIVITY->value)
            ->by(auth()->user())
            ->on($role)
            ->withProperties([
                'attributes' => $changedAttributes,
                'old' => array_intersect_key($oldAttributes, $changedAttributes),
            ])
            ->event(ActivityEvent::UPDATED->value)
            ->log('Role is updated!');
    }
}
