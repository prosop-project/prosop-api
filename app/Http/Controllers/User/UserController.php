<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * TODO: $user->sendEmailVerificationNotification(); should be done similar to register user in case new email is set - for the places like update etc.
 *
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
     *
     * @return ProfileResource
     */
    public function update(UpdateUserRequest $request, User $user): ProfileResource
    {
        $validatedRequest = $request->validated();

        // If the email is being changed, reset verification.
        if ($request->has('email') && $user->email !== $request->input('email')) {
            $validatedRequest = array_merge($validatedRequest, ['email_verified_at' => null]);
        }

        // Update the user with the validated data.
        $user->update($validatedRequest);

        // Invalidate the old token.
        JWTAuth::parseToken()->invalidate(true);
        // Generate a new token for the updated user.
        $token = JWTAuth::fromUser($user);

        // Load the user links and updated user.
        $user = $user->fresh()?->load('links');

        return (new ProfileResource($user))->additional(['token' => $token]);
    }

    /**
     * Delete a user.
     *
     * @param DeleteUserRequest $_
     * @param User $user
     *
     * @return JsonResponse
     */
    public function delete(DeleteUserRequest $_, User $user): JsonResponse
    {
        // Invalidate the token if it exists.
        if (JWTAuth::getToken()) {
            JWTAuth::parseToken()->invalidate(true);
        }

        // Delete the user.
        $user->delete();

        return response()->json(['message' => 'User deleted successfully!']);
    }

    /**
     * Get the authenticated user including the links relation.
     *
     * TODO: other relations will be added to ProfileResource resource - and eager loading such as settings etc.
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
