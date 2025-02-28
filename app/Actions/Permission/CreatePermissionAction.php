<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Http\Requests\Permission\PermissionRequest;
use App\Services\ActivityLog\CreatePermissionModelActivityService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * @class CreatePermissionAction
 */
final readonly class CreatePermissionAction
{
    /**
     * @param CreatePermissionModelActivityService $activityService
     */
    public function __construct(private CreatePermissionModelActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param PermissionRequest $request
     *
     * @return Permission
     */
    public function handle(PermissionRequest $request): Permission
    {
        return DB::transaction(function () use ($request) {
            // Create a new permission
            $permission = Permission::query()->create([
                'name' => $request->name,
            ]);

            // Log the activity
            $this->activityService->log($permission);

            return $permission;
        });
    }
}
