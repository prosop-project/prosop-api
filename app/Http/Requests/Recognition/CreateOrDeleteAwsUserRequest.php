<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Http\Requests\BaseRequest;

/**
 * CreateOrDeleteAwsUserRequest is the form request that handles the validation of the create/delete user request to AWS Rekognition.
 *
 * @property string $aws_collection_id
 * @property int $user_id
 * @property string|null $client_request_token
 *
 * @property-read string $external_user_id
 *
 * @class CreateOrDeleteAwsUserRequest
 */
final class CreateOrDeleteAwsUserRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): bool
    {
        return (auth()->id() === (int) $this->input('user_id'))
            || is_admin();
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'aws_collection_id' => ['required', 'string', 'exists:aws_collections,id'],
            'user_id' => ['required', 'integer'],
            'client_request_token' => ['nullable', 'string'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function passedValidation(): void
    {
        $this->merge([
            'external_user_id' => config('aws-rekognition.reference_prefix') . '-' . $this->input('user_id'),
        ]);
    }
}
