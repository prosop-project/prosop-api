<?php

declare(strict_types=1);

namespace App\Http\Requests\Subscription;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Validator;

/**
 * UnsubscribeRequest is the form request that handles the validation of the unsubscribe request.
 *
 * @class UnsubscribeRequest
 */
final class UnsubscribeRequest extends BaseRequest
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
            $userId = $this->route('user')->id; // Get user id from the route
            $subscriberId = auth()->id(); // Get the authenticated user id

            if (($subscriberId === $userId)) {
                $validator->errors()->add('subscribe.yourself', 'You cannot subscribe/unsubscribe yourself.');
            }
        });
    }
}
