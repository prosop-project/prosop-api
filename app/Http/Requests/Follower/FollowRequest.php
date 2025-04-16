<?php

declare(strict_types=1);

namespace App\Http\Requests\Follower;

use App\Http\Requests\BaseRequest;
use App\Models\Follower;
use Illuminate\Validation\Validator;

/**
 * FollowRequest is the form request that handles the validation of the follow request.
 *
 * @class FollowRequest
 */
final class FollowRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * The additional validation is called after the rules method.
     *
     * @param Validator $validator
     *
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->route('user')->id; // Get user from the route
            $followerId = auth()->id(); // Get the authenticated user id

            if (($followerId === $userId)) {
                $validator->errors()->add('follow.yourself', 'You cannot follow yourself.');
            }

            // Check if the record already exists
            $exists = Follower::query()
                ->where('user_id', $userId)
                ->where('follower_id', $followerId)
                ->exists();

            if ($exists) {
                $validator->errors()->add('follow.unique', 'You are already following this user.');
            }
        });
    }
}
