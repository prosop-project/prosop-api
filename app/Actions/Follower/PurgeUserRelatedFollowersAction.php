<?php

declare(strict_types=1);

namespace App\Actions\Follower;

use App\Models\Follower;
use App\Models\User;

/**
 * @class PurgeUserRelatedFollowersAction
 */
final readonly class PurgeUserRelatedFollowersAction
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
        // Get the follower/following ids of the user.
        $followerIds = Follower::query()
            ->where('follower_id', $user->id)
            ->orWhere('user_id', $user->id)
            ->pluck('id');

        // Delete the user related followers from the database (It triggers activity logs because destroy loads models before deleting them).
        Follower::destroy($followerIds);
    }
}
