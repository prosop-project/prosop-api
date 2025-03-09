<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Http\Requests\Recognition\CreateOrDeleteAwsUserRequest;
use App\Models\AwsUser;

/**
 * @class CreateAwsUserAction
 */
final readonly class CreateAwsUserAction
{
    /**
     * Handle the action.
     *
     * @param CreateOrDeleteAwsUserRequest $request
     *
     * @return AwsUser
     */
    public function handle(CreateOrDeleteAwsUserRequest $request): AwsUser
    {
        return AwsUser::query()->create([
            'user_id' => $request->user_id,
            'aws_collection_id' => $request->aws_collection_id,
            'external_user_id' => $request->external_user_id,
        ]);
    }
}
