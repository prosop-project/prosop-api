<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Http\Requests\Permission\PermissionRequest;
use App\Services\ActivityLog\UpdatePermissionModelActivityService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * @class UpdatePermissionAction
 */
final readonly class UpdatePermissionAction
{
    /**
     * @param UpdatePermissionModelActivityService $activityService
     */
    public function __construct(private UpdatePermissionModelActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param PermissionRequest $request
     * @param Permission $permission
     *
     * @return void
     */
    public function handle(PermissionRequest $request, Permission $permission): void
    {
        DB::transaction(function () use ($request, $permission) {
            // Retrieve the old attributes before updating
            $oldAttributes = $permission->getOriginal();

            // Update the permission
            $permission->update($request->validated());

            // Retrieve the changed attributes
            $changedAttributes = $permission->getChanges();

            // Log the activity
            $this->activityService->log($permission, $oldAttributes, $changedAttributes);
        });
    }
}
