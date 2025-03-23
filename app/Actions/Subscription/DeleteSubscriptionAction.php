<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Models\Subscription;
use App\Models\User;

/**
 * @class DeleteSubscriptionAction
 */
final readonly class DeleteSubscriptionAction
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
        // Delete the subscription (Note: firstOrFail will make sure activity log is triggered - doing direct delete will not trigger activity log)
        Subscription::query()
            ->where('subscriber_id', auth()->id())
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->delete();
    }
}
