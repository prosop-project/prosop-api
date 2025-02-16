<?php

declare(strict_types=1);

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\AssignOrRemoveRoleRequest;
use App\Http\Requests\Permission\PermissionRequest;
use App\Http\Requests\Permission\RoleRequest;
use App\Http\Resources\RolePermissionResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @class PermissionController
 */
final readonly class PermissionController extends Controller
{
    /*
     | ---------------------------------------------------------
     | Here CRUD operations for the permissions are implemented.
     | ---------------------------------------------------------
     */

    /**
     * Create a new permission.
     *
     * @param PermissionRequest $request
     *
     * @return RolePermissionResource
     */
    public function createPermission(PermissionRequest $request): RolePermissionResource
    {
        $permission = Permission::query()->create([
            'name' => $request->name,
        ]);

        return new RolePermissionResource($permission);
    }

    /**
     * Get all permissions.
     *
     * @return AnonymousResourceCollection
     */
    public function permissions(): AnonymousResourceCollection
    {
        return RolePermissionResource::collection(Permission::all());
    }

    /**
     * Delete a permission.
     *
     * @param Permission $permission
     *
     * @return JsonResponse
     */
    public function deletePermission(Permission $permission): JsonResponse
    {
        // Delete the permission.
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully!']);
    }

    /**
     * Update a permission.
     *
     * @param PermissionRequest $request
     * @param Permission $permission
     *
     * @return JsonResponse
     */
    public function updatePermission(PermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission->update($request->validated());

        return response()->json(['message' => 'Permission updated successfully!']);
    }

    /*
     | ---------------------------------------------------
     | Here CRUD operations for the roles are implemented.
     | ---------------------------------------------------
     */

    /**
     * Create a new role.
     *
     * @param RoleRequest $request
     *
     * @return RolePermissionResource
     */
    public function createRole(RoleRequest $request): RolePermissionResource
    {
        $role = Role::query()->create($request->validated());

        return new RolePermissionResource($role);
    }

    /**
     * Update a role.
     *
     * @param RoleRequest $request
     * @param Role $role
     *
     * @return JsonResponse
     */
    public function updateRole(RoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());

        return response()->json(['message' => 'Role updated successfully!']);
    }

    /**
     * Delete a role.
     *
     * @param Role $role
     *
     * @return JsonResponse
     */
    public function deleteRole(Role $role): JsonResponse
    {
        // Delete the role.
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully!']);
    }

    /**
     * Get all roles.
     *
     * @return AnonymousResourceCollection
     */
    public function roles(): AnonymousResourceCollection
    {
        return RolePermissionResource::collection(Role::all());
    }

    /*
     | ---------------------------------------------------
     | Here attaching permissions to roles are implemented.
     | ---------------------------------------------------
     */

    /**
     * Grant permission to role.
     *
     * @param Role $role
     * @param Permission $permission
     *
     * @return JsonResponse
     */
    public function grantPermissionToRole(Role $role, Permission $permission): JsonResponse
    {
        $role->givePermissionTo($permission);

        return response()->json(['message' => 'Permission granted to role successfully!']);
    }

    /*
     | ---------------------------------------------------
     | Here assigning and revoking roles to users are implemented.
     | ---------------------------------------------------
     */

    /**
     * Assign role to user.
     *
     * @param AssignOrRemoveRoleRequest $request
     * @param User $user
     *
     * @return JsonResponse
     */
    public function assignRole(AssignOrRemoveRoleRequest $request, User $user): JsonResponse
    {
        $user->assignRole($request->validated('role'));

        return response()->json(['message' => 'Role assigned successfully!']);
    }

    /**
     * Remove role from user.
     *
     * @param AssignOrRemoveRoleRequest $request
     * @param User $user
     *
     * @return JsonResponse
     */
    public function removeRole(AssignOrRemoveRoleRequest $request, User $user): JsonResponse
    {
        $user->removeRole($request->validated('role'));

        return response()->json(['message' => 'Role removed successfully!']);
    }
}
