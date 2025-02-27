<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Actions\Subscription\DeleteUserAction;
use App\Actions\User\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\GenericResponseResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Arr;

/**
 * @class UserController
 */
final readonly class UserController extends Controller
{
    /**
     * Display a single user by wrapping it in the UserResource.
     *
     * @param User $user
     *
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        $user->load([
            'links' => function ($query) {
                $query->where('is_visible', true);
            },
        ]);

        return new UserResource($user);
    }

    /**
     * Update the user.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @param UpdateUserAction $updateUserAction
     *
     * @return ProfileResource
     */
    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUserAction $updateUserAction
    ): ProfileResource {
        $response = $updateUserAction->handle($request, $user);

        return (new ProfileResource(Arr::get($response, 'user')))
            ->additional(['token' => Arr::get($response, 'token')]);
    }

    /**
     * Delete a user.
     *
     * @param DeleteUserRequest $_
     * @param User $user
     * @param DeleteUserAction $deleteUserAction
     *
     * @return GenericResponseResource
     */
    public function delete(
        DeleteUserRequest $_,
        User $user,
        DeleteUserAction $deleteUserAction
    ): GenericResponseResource {
        $deleteUserAction->handle($user);

        return new GenericResponseResource('User deleted successfully!');
    }

    /**
     * Get the authenticated user including the link's relation.
     *
     * @return ProfileResource
     */
    public function profile(): ProfileResource
    {
        // Get the authenticated user.
        $user = auth()->user();

        // Load user links including both visible and invisible links.
        $user?->load(['links']);

        return new ProfileResource($user);
    }
}
