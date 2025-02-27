<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\UserTokenData;
use App\Services\ActivityLog\LoginUserActivityService;
use Illuminate\Support\Facades\Auth;

/**
 * @class LoginAction
 */
final readonly class LoginAction
{
    /**
     * @param LoginUserActivityService $activityService
     */
    public function __construct(protected LoginUserActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param mixed $token
     *
     * @return UserTokenData
     */
    public function handle(mixed $token): UserTokenData
    {
        // Get the authenticated user.
        $user = Auth::getUser();

        // Log the activity.
        $this->activityService->log($user);

        // Create the user token data and return it.
        return new UserTokenData(
            user: $user,
            message: 'Login successful!',
            token: $token,
        );
    }
}
