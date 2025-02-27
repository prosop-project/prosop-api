<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use Spatie\Permission\Models\Permission;

/**
 * @class UpdatePermissionModelActivityService
 */
final readonly class UpdatePermissionModelActivityService
{
    /**
     * Log the activity.
     *
     * @param Permission $permission
     * @param mixed $oldAttributes
     * @param mixed $changedAttributes
     *
     * @return void
     */
    public function log(Permission $permission, mixed $oldAttributes, mixed $changedAttributes): void
    {
        activity(ActivityLogName::PERMISSION_MODEL_ACTIVITY->value)
            ->by(auth()->user())
            ->on($permission)
            ->withProperties([
                'attributes' => $changedAttributes,
                'old' => array_intersect_key($oldAttributes, $changedAttributes),
            ])
            ->event(ActivityEvent::UPDATED->value)
            ->log('Permission is updated!');
    }
}
