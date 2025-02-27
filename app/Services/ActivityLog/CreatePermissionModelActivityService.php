<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use Spatie\Permission\Models\Permission;

/**
 * @class CreatePermissionModelActivityService
 */
final readonly class CreatePermissionModelActivityService
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
                'attributes' => $permission->getAttributes(),
            ])
            ->event(ActivityEvent::CREATED->value)
            ->log('Permission is created!');
    }
}
