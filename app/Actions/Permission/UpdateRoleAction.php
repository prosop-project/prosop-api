<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Http\Requests\Permission\RoleRequest;
use App\Services\ActivityLog\UpdateRoleModelActivityService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * @class UpdateRoleAction
 */
final readonly class UpdateRoleAction
{
    /**
     * @param UpdateRoleModelActivityService $activityService
     */
    public function __construct(private UpdateRoleModelActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param RoleRequest $request
     * @param Role $role
     *
     * @return void
     */
    public function handle(RoleRequest $request, Role $role): void
    {
        DB::transaction(function () use ($request, $role) {
            // Retrieve the original attributes of the role before updating
            $oldAttributes = $role->getOriginal();

            // Update the role
            $role->update($request->validated());

            // Retrieve the changed attributes of the role after updating
            $changedAttributes = $role->getChanges();

            // Log the activity
            $this->activityService->log($role, $oldAttributes, $changedAttributes);
        });
    }
}
