<?php

declare(strict_types=1);

use App\Http\Controllers\ActivityLog\ActivityLogController;
use App\Http\Controllers\Analysis\AnalysisController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Link\LinkController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Recognition\AwsRekognitionController;
use App\Http\Controllers\Follower\FollowerController;
use App\Http\Controllers\User\UserController;
use App\Http\Middleware\ValidateUserIsAdmin;
use Illuminate\Support\Facades\Route;

/*
 | -------------------------------------------
 | Endpoints for managing user authentication.
 | -------------------------------------------
 */
Route::middleware(['throttle:5,1'])->prefix('auth')->name('auth.')->group(function() {
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
 | Endpoints for managing followers.
 |--------------------------------------
 */
Route::prefix('followers')->name('followers.')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::post('/{user}', [FollowerController::class, 'follow'])->name('follow');
        Route::delete('/{user}', [FollowerController::class, 'unfollow'])->name('unfollow');
    });

    Route::get('/{user}', [FollowerController::class, 'following'])->name('following');
    Route::get('/{user}/followers', [FollowerController::class, 'followers'])->name('followers');
});

/*
 |--------------------------------------
 | Endpoints for recognition operations.
 |--------------------------------------
 */
Route::prefix('recognition')->name('recognition.')->group(function () {
    /*
     * Endpoints for managing aws collections.
     */
    Route::middleware(['auth:api', ValidateUserIsAdmin::class])->group(function () {
        Route::post('/create_collection', [AwsRekognitionController::class, 'createCollection'])->name('create.collection');
        Route::get('/external/list_collections', [AwsRekognitionController::class, 'listExternalCollections'])->name('external.list.collections');
        Route::get('/aws_collections', [AwsRekognitionController::class, 'getAwsCollections'])->name('aws.collections');
        Route::delete('/delete_collection/{awsCollection}', [AwsRekognitionController::class, 'deleteCollection'])->name('delete.collection');
    });

    /*
     * Endpoints for managing aws users.
     */
    Route::middleware(['auth:api'])->group(function () {
        Route::post('/create_aws_user', [AwsRekognitionController::class, 'createAwsUser'])->name('create.aws.user');
        Route::delete('/delete_aws_user', [AwsRekognitionController::class, 'deleteAwsUser'])->name('delete.aws.user');

        // Getting aws users (both database aws_users and AWS Rekognition side users) is only allowed for admin users.
        Route::middleware(ValidateUserIsAdmin::class)->group(function () {
            Route::get('/aws_users', [AwsRekognitionController::class, 'getAwsUsers'])->name('aws.users');
            Route::get('/external/list_aws_users', [AwsRekognitionController::class, 'listExternalAwsUsers'])->name('external.list.users');
        });
    });

    /*
     * Endpoints for managing aws faces.
     */
    Route::middleware(['auth:api'])->group(function () {
        Route::middleware(['throttle:5,1'])->post('/process_faces/{user}', [AwsRekognitionController::class, 'processFaces'])->name('process.faces');

        // Getting/deleting aws faces (both database aws_faces and AWS Rekognition side faces) is only allowed for admin users.
        Route::middleware(ValidateUserIsAdmin::class)->group(function () {
            Route::get('/aws_faces/{user}', [AwsRekognitionController::class, 'getAwsFaces'])->name('aws.faces');
            Route::get('/external/list_faces', [AwsRekognitionController::class, 'listExternalFaces'])->name('external.list.faces');
            Route::delete('/delete_faces', [AwsRekognitionController::class, 'deleteFaces'])->name('delete.faces');
        });
    });

    /*
     * Endpoints for searching collection for matching faces, user ids and so on.
     */
    Route::prefix('search')->group(function () {
        Route::middleware(['throttle:5,1'])->post('/collection', [AwsRekognitionController::class, 'searchCollection'])->name('search.collection');
    });
});

/*
 |--------------------------------------
 | Endpoints for managing analysis operations.
 |--------------------------------------
 */
Route::prefix('analysis')->name('analysis.')->group(function () {
    Route::prefix('{public_uuid}')->group(function () {
        Route::get('/', [AnalysisController::class, 'getUserAnalysisOperations'])->name('operations.user');

        /*
         * Endpoints for managing analysis operations and aws similarity results for a specific analysis operation.
         */
        Route::prefix('analysis_operation/{analysis_operation_id}')->group(function () {
            Route::delete('/', [AnalysisController::class, 'deleteAnalysisOperation'])
                ->name('delete.operation');
            Route::delete('/aws_similarity_result/{aws_similarity_result_id}', [AnalysisController::class, 'deleteAwsSimilarityResult'])
                ->name('delete.aws.similarity.result');
        });
    });
});
