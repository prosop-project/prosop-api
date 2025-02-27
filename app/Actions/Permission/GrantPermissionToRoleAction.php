<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Services\ActivityLog\GrantPermissionToRoleActivityService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @class GrantPermissionToRoleAction
 */
final readonly class GrantPermissionToRoleAction
{
    /**
     * @param GrantPermissionToRoleActivityService $activityService
     */
    public function __construct(protected GrantPermissionToRoleActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param Role $role
     * @param Permission $permission
     *
     * @return void
     */
    public function handle(Role $role, Permission $permission): void
    {
        DB::transaction(function () use ($role, $permission) {
            // Grant the permission to the role.
            $role->givePermissionTo($permission);

            // Log the activity.
            $this->activityService->log($role, $permission);
        });
    }
}
