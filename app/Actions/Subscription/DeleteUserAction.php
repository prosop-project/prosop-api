<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @class DeleteUserAction
 */
final readonly class DeleteUserAction
{
    /**
     * Handle the action.
     *
     * @param User $user
     *
     * @return void
     */
    public function handle(User $user): void
    {
        // Invalidate the token if it exists.
        if (JWTAuth::getToken()) {
            JWTAuth::parseToken()->invalidate(true);
        }

        // Delete the user.
        $user->delete();
    }
}
