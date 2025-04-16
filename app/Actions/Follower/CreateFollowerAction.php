<?php

declare(strict_types=1);

namespace App\Actions\Follower;

use App\Models\Follower;
use App\Models\User;

/**
 * @class CreateFollowerAction
 */
final readonly class CreateFollowerAction
{
    /**
     * Handle the action.
     *
     * @param User $user
     *
     * @return Follower
     */
    public function handle(User $user): Follower
    {
        // Create a new follower where the authenticated user will follow the provided user
        return Follower::query()->create([
            'user_id' => $user->id,
            'follower_id' => auth()->id(),
        ]);
    }
}
