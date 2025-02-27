<?php

declare(strict_types=1);

use App\Http\Controllers\ActivityLog\ActivityLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Link\LinkController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\User\UserController;
use App\Http\Middleware\ValidateUserIsAdmin;
use Illuminate\Support\Facades\Route;

/*
 | -------------------------------------------
 | Endpoints for managing user authentication.
 | -------------------------------------------
 */
Route::prefix('auth')->name('auth.')->group(function() {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
 | ---------------------------------------------------------
 | Endpoints for admin users only.
 | ---------------------------------------------------------
 */
Route::middleware(['auth:api', ValidateUserIsAdmin::class])->group(function () {
    /*
     | ---------------------------------------------------------
     | Endpoints for managing permissions, roles and user roles.
     | ---------------------------------------------------------
     */
    Route::prefix('permissions')->name('permissions.')->group(function () {
        /*
         * Endpoints for managing permissions crud operations.
         */
        Route::get('/list', [PermissionController::class, 'permissions'])->name('list.permissions');
        Route::post('/create_permission', [PermissionController::class, 'createPermission'])->name('create.permission');
        Route::delete('/delete_permission/{permission}', [PermissionController::class, 'deletePermission'])->name('delete.permission');
        Route::patch('/update_permission/{permission}', [PermissionController::class, 'updatePermission'])->name('update.permission');

        /*
         * Endpoints for managing roles crud operations.
         */
        Route::get('/roles', [PermissionController::class, 'roles'])->name('list.roles');
        Route::post('/create_role', [PermissionController::class, 'createRole'])->name('create.role');
        Route::delete('/delete_role/{role}', [PermissionController::class, 'deleteRole'])->name('delete.role');
        Route::patch('/update_role/{role}', [PermissionController::class, 'updateRole'])->name('update.role');

        /*
         * Endpoint to grant permission to role.
         */
        Route::post('/role/{role}/permission/{permission}', [PermissionController::class, 'grantPermissionToRole'])->name('grant.permission');

        /*
         * Endpoint to assign and revoke role to user.
         */
        Route::prefix('users')->name('users.')->group(function () {
            Route::post('/{user}/assign_role', [PermissionController::class, 'assignRole'])->name('assign.role');
            Route::delete('/{user}/remove_role', [PermissionController::class, 'removeRole'])->name('remove.role');
        });
    });

    /*
     * Endpoint for cleaning activity log records.
     */
    Route::delete('/activity_log/clean', ActivityLogController::class)->name('activity_log.clean');
});

/*
 |--------------------------------------------
 | Endpoints for managing user crud operations.
 |--------------------------------------------
 */
Route::prefix('users')->name('users.')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::delete('/{user}', [UserController::class, 'delete'])->name('delete');
        Route::patch('/{user}', [UserController::class, 'update'])->name('update');
    });

    Route::get('/{user}', [UserController::class, 'show'])->name('show');
});

/*
 |----------------------------------------------
 | Endpoints for managing links crud operations.
 |----------------------------------------------
 */
Route::middleware('auth:api')->prefix('links')->name('links.')->group(function () {
    Route::post('/{user}', [LinkController::class, 'create'])->name('create');
    Route::delete('/{link}', [LinkController::class, 'delete'])->name('delete');
    Route::patch('/{link}', [LinkController::class, 'update'])->name('update');
});

/*
 |--------------------------------------
 | Endpoints for managing subscriptions.
 |--------------------------------------
 */
Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::post('/{user}', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::delete('/{user}', [SubscriptionController::class, 'unsubscribe'])->name('unsubscribe');
    });

    Route::get('/{user}', [SubscriptionController::class, 'subscriptions'])->name('list');
    Route::get('/{user}/subscribers', [SubscriptionController::class, 'subscribers'])->name('subscribers');
});
