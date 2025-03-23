<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Models\AwsUser;

/**
 * @class UpdateAwsUserAction
 */
final readonly class UpdateAwsUserAction
{
    /**
     * Handle the action.
     *
     * @param AwsUser $awsUser
     * @param string|null $externalUserStatus
     *
     * @return void
     */
    public function handle(AwsUser $awsUser, ?string $externalUserStatus): void
    {
        // Update the external user status.
        $awsUser->update([
            'external_user_status' => $externalUserStatus,
        ]);
    }
}
