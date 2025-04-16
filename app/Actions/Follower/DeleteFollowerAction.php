<?php

declare(strict_types=1);

namespace App\Actions\Follower;

use App\Models\Follower;
use App\Models\User;

/**
 * @class DeleteFollowerAction
 */
final readonly class DeleteFollowerAction
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
        // Delete the follower (Note: firstOrFail will make sure activity log is triggered - doing direct delete will not trigger activity log)
        Follower::query()
            ->where('follower_id', auth()->id())
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->delete();
    }
}
