<?php

declare(strict_types=1);

namespace App\Http\Controllers\Permission;

use App\Actions\Permission\AssignRoleToUserAction;
use App\Actions\Permission\CreatePermissionAction;
use App\Actions\Permission\CreateRoleAction;
use App\Actions\Permission\DeletePermissionAction;
use App\Actions\Permission\DeleteRoleAction;
use App\Actions\Permission\GrantPermissionToRoleAction;
use App\Actions\Permission\RemoveRoleFromUserAction;
use App\Actions\Permission\UpdatePermissionAction;
use App\Actions\Permission\UpdateRoleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\AssignOrRemoveRoleRequest;
use App\Http\Requests\Permission\PermissionRequest;
use App\Http\Requests\Permission\RoleRequest;
use App\Http\Resources\GenericResponseResource;
use App\Http\Resources\RolePermissionResource;
use App\Models\User;
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
     * @param CreatePermissionAction $createPermissionAction
     *
     * @return RolePermissionResource
     */
    public function createPermission(
        PermissionRequest $request,
        CreatePermissionAction $createPermissionAction
    ): RolePermissionResource {
        $permission = $createPermissionAction->handle($request);

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
     * @param DeletePermissionAction $deletePermissionAction
     *
     * @return GenericResponseResource
     */
    public function deletePermission(
        Permission $permission,
        DeletePermissionAction $deletePermissionAction
    ): GenericResponseResource {
        $deletePermissionAction->handle($permission);

        return new GenericResponseResource('Permission deleted successfully!');
    }

    /**
     * Update a permission.
     *
     * @param PermissionRequest $request
     * @param Permission $permission
     * @param UpdatePermissionAction $updatePermissionAction
     *
     * @return GenericResponseResource
     */
    public function updatePermission(
        PermissionRequest $request,
        Permission $permission,
        UpdatePermissionAction $updatePermissionAction
    ): GenericResponseResource {
        $updatePermissionAction->handle($request, $permission);

        return new GenericResponseResource('Permission updated successfully!');
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
     * @param CreateRoleAction $createRoleAction
     *
     * @return RolePermissionResource
     */
    public function createRole(
        RoleRequest $request,
        CreateRoleAction $createRoleAction
    ): RolePermissionResource {
        $role = $createRoleAction->handle($request);

        return new RolePermissionResource($role);
    }

    /**
     * Update a role.
     *
     * @param RoleRequest $request
     * @param Role $role
     * @param UpdateRoleAction $updateRoleAction
     *
     * @return GenericResponseResource
     */
    public function updateRole(
        RoleRequest $request,
        Role $role,
        UpdateRoleAction $updateRoleAction
    ): GenericResponseResource {
        $updateRoleAction->handle($request, $role);

        return new GenericResponseResource('Role updated successfully!');
    }

    /**
     * Delete a role.
     *
     * @param Role $role
     * @param DeleteRoleAction $deleteRoleAction
     *
     * @return GenericResponseResource
     */
    public function deleteRole(Role $role, DeleteRoleAction $deleteRoleAction): GenericResponseResource
    {
        $deleteRoleAction->handle($role);

        return new GenericResponseResource('Role deleted successfully!');
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
     * @param GrantPermissionToRoleAction $grantPermissionToRoleAction
     *
     * @return GenericResponseResource
     */
    public function grantPermissionToRole(
        Role $role,
        Permission $permission,
        GrantPermissionToRoleAction $grantPermissionToRoleAction
    ): GenericResponseResource {
        $grantPermissionToRoleAction->handle($role, $permission);

        return new GenericResponseResource('Permission granted to the role successfully!');
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
     * @param AssignRoleToUserAction $assignRoleToUserAction
     *
     * @return GenericResponseResource
     */
    public function assignRole(
        AssignOrRemoveRoleRequest $request,
        User $user,
        AssignRoleToUserAction $assignRoleToUserAction
    ): GenericResponseResource {
        $assignRoleToUserAction->handle($request, $user);

        return new GenericResponseResource('Role assigned to the user successfully!');
    }

    /**
     * Remove role from user.
     *
     * @param AssignOrRemoveRoleRequest $request
     * @param User $user
     * @param RemoveRoleFromUserAction $removeRoleFromUserAction
     *
     * @return GenericResponseResource
     */
    public function removeRole(
        AssignOrRemoveRoleRequest $request,
        User $user,
        RemoveRoleFromUserAction $removeRoleFromUserAction
    ): GenericResponseResource {
        $removeRoleFromUserAction->handle($request, $user);

        return new GenericResponseResource('Role removed from the user successfully!');
    }
}
