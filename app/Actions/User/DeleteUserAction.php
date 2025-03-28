<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Actions\Analysis\DeleteAnalysisOperationAction;
use App\Actions\Link\DeleteLinkAction;
use App\Actions\Recognition\DeleteAwsUserAction;
use App\Actions\Subscription\PurgeUserSubscriptionsAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @class DeleteUserAction
 */
final readonly class DeleteUserAction
{
    /**
     * @param DeleteLinkAction $deleteLinkAction
     * @param DeleteAwsUserAction $deleteAwsUserAction
     * @param DeleteAnalysisOperationAction $deleteAnalysisOperationAction
     * @param PurgeUserSubscriptionsAction $purgeUserSubscriptionsAction
     */
    public function __construct(
        private DeleteLinkAction $deleteLinkAction,
        private DeleteAwsUserAction $deleteAwsUserAction,
        private DeleteAnalysisOperationAction $deleteAnalysisOperationAction,
        private PurgeUserSubscriptionsAction $purgeUserSubscriptionsAction,
    ) {}


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

        // Load the user with the related models.
        $user->load([
            'links',
            'awsUsers',
            'analysisOperations'
        ]);

        /*
         * Handle all deletions in a transaction.
         * Delete the user's related models (manually cascade delete). And then delete the user.
         */
        DB::transaction(function () use ($user) {
            /*
             * Delete the related link(s) from the link model/table.
             */
            $user->links->each(function ($link) {
                $this->deleteLinkAction->handle($link);
            });

            /*
             * Delete the related aws_user(s) model/table (also its related models such as aws faces), and aws side user (external user).
             */
            $user->awsUsers->each(function ($awsUser) {
                $this->deleteAwsUserAction->handle($awsUser);
            });

            /*
             * Delete the analysis operation(s) from the analysis_operation model/table.
             */
            $user->analysisOperations->each(function ($analysisOperation) {
                $this->deleteAnalysisOperationAction->handle($analysisOperation);
            });

            /*
             * Delete the subscriptions from the subscription model/table (Purge the user's subscriptions - by checking both subscriber_id and user_id).
             */
            $this->purgeUserSubscriptionsAction->handle($user);

            /*
             * Delete the user from the user model/table.
             */
            $user->delete();
        });
    }
}
