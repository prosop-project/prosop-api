<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use Spatie\Permission\Models\Permission;

/**
 * @class DeletePermissionModelActivityService
 */
final readonly class DeletePermissionModelActivityService
{
    /**
     * Log the activity.
     *
     * @param Permission $permission
     *
     * @return void
     */
    public function log(Permission $permission): void
    {
        activity(ActivityLogName::PERMISSION_MODEL_ACTIVITY->value)
            ->by(auth()->user())
            ->on($permission)
            ->withProperties([
                'old' => $permission->getAttributes(),
            ])
            ->event(ActivityEvent::DELETED->value)
            ->log('Permission is deleted!');
    }
}
