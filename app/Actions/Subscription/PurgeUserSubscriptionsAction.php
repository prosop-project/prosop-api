<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Models\Subscription;
use App\Models\User;

/**
 * @class PurgeUserSubscriptionsAction
 */
final readonly class PurgeUserSubscriptionsAction
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
        // Get the subscription ids of the user.
        $subscriptionIds = Subscription::query()
            ->where('subscriber_id', $user->id)
            ->orWhere('user_id', $user->id)
            ->pluck('id');

        // Delete the subscriptions from the database (It triggers activity logs because destroy loads models before deleting them).
        Subscription::destroy($subscriptionIds);
    }
}
