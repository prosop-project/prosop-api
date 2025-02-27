<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Services\ActivityLog\DeletePermissionModelActivityService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * @class DeletePermissionAction
 */
final readonly class DeletePermissionAction
{
    /**
     * @param DeletePermissionModelActivityService $activityService
     */
    public function __construct(protected DeletePermissionModelActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param Permission $permission
     *
     * @return void
     */
    public function handle(Permission $permission): void
    {
        DB::transaction(function () use ($permission) {
            // Delete the permission.
            $permission->delete();

            // Log the activity.
            $this->activityService->log($permission);
        });
    }
}
