<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Services\ActivityLog\DeleteRoleModelActivityService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * @class DeleteRoleAction
 */
final readonly class DeleteRoleAction
{
    /**
     * @param DeleteRoleModelActivityService $activityService
     */
    public function __construct(private DeleteRoleModelActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param Role $role
     *
     * @return void
     */
    public function handle(Role $role): void
    {
        DB::transaction(function () use ($role) {
            // Delete the role.
            $role->delete();

            // Log the activity.
            $this->activityService->log($role);
        });
    }
}
