<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Http\Requests\Permission\AssignOrRemoveRoleRequest;
use App\Models\User;
use App\Services\ActivityLog\RemoveRoleFromUserActivityService;
use Illuminate\Support\Facades\DB;

/**
 * @class RemoveRoleFromUserAction
 */
final readonly class RemoveRoleFromUserAction
{
    /**
     * @param RemoveRoleFromUserActivityService $activityService
     */
    public function __construct(private RemoveRoleFromUserActivityService $activityService) {}

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
            // Remove the role from the user.
            $user->removeRole($request->validated('role'));

            // Log the activity.
            $this->activityService->log($request, $user);
        });
    }
}
