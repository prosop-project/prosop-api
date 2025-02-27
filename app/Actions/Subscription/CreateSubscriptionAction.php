<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Models\Subscription;
use App\Models\User;

/**
 * @class CreateSubscriptionAction
 */
final readonly class CreateSubscriptionAction
{
    /**
     * Handle the action.
     *
     * @param User $user
     *
     * @return Subscription
     */
    public function handle(User $user): Subscription
    {
        // Create a new subscription for the authenticated user.
        return Subscription::query()->create([
            'user_id' => $user->id,
            'subscriber_id' => auth()->id(),
        ]);
    }
}
