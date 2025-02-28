<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Http\Requests\Permission\RoleRequest;
use App\Services\ActivityLog\CreateRoleModelActivityService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * @class CreateRoleAction
 */
final readonly class CreateRoleAction
{
    /**
     * @param CreateRoleModelActivityService $activityService
     */
    public function __construct(private CreateRoleModelActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param RoleRequest $request
     *
     * @return Role
     */
    public function handle(RoleRequest $request): Role
    {
        return DB::transaction(function () use ($request) {
            // Create the role
            $role = Role::query()->create($request->validated());

            // Log the activity
            $this->activityService->log($role);

            return $role;
        });
    }
}
