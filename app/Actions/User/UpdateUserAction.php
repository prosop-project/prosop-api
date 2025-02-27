<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @class UpdateUserAction
 */
final readonly class UpdateUserAction
{
    /**
     * Handle the action.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     *
     * @return array<string, mixed>
     */
    public function handle(UpdateUserRequest $request, User $user): array
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

        return ['user' => $user, 'token' => $token];
    }
}
