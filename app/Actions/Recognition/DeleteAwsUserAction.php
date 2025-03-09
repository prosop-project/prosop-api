<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Http\Requests\Recognition\CreateOrDeleteAwsUserRequest;
use App\Models\AwsUser;

/**
 * @class DeleteAwsUserAction
 */
final readonly class DeleteAwsUserAction
{
    /**
     * Handle the action.
     *
     * @param CreateOrDeleteAwsUserRequest $request
     *
     * @return void
     */
    public function handle(CreateOrDeleteAwsUserRequest $request): void
    {
        // Delete the AWS user (Note: firstOrFail will make sure activity log is triggered - doing direct delete will not trigger activity log)
        AwsUser::query()
            ->where('aws_collection_id', $request->aws_collection_id)
            ->where('external_user_id', $request->external_user_id)
            ->firstOrFail()
            ->delete();
    }
}
