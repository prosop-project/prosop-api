<?php

declare(strict_types=1);

namespace App\Http\Requests\Subscription;

use App\Http\Requests\BaseRequest;
use App\Models\Subscription;
use Illuminate\Validation\Validator;

/**
 * SubscribeRequest is the form request that handles the validation of the subscribe request.
 *
 * @class SubscribeRequest
 */
final class SubscribeRequest extends BaseRequest
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
            $subscriberId = auth()->id(); // Get the authenticated user id

            if (($subscriberId === $userId)) {
                $validator->errors()->add('subscribe.yourself', 'You cannot subscribe/unsubscribe yourself.');
            }

            // Check if subscription already exists
            $exists = Subscription::query()
                ->where('user_id', $userId)
                ->where('subscriber_id', $subscriberId)
                ->exists();

            if ($exists) {
                $validator->errors()->add('subscribe.unique', 'You are already subscribed to this user.');
            }
        });
    }
}
