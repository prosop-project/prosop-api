<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Http\Requests\Permission\AssignOrRemoveRoleRequest;
use App\Models\User;
use App\Services\ActivityLog\AssignRoleToUserActivityService;
use Illuminate\Support\Facades\DB;

/**
 * @class AssignRoleToUserAction
 */
final readonly class AssignRoleToUserAction
{
    /**
     * @param AssignRoleToUserActivityService $activityService
     */
    public function __construct(protected AssignRoleToUserActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param AssignOrRemoveRoleRequest $request
     * @param User $user
     *
     * @return void
     */
    public function handle(AssignOrRemoveRoleRequest $request, User $user): void
    {
        DB::transaction(function () use ($request, $user) {
            // Assign the role to the user
            $user->assignRole($request->validated('role'));

            // Log the activity
            $this->activityService->log($request, $user);
        });
    }
}
