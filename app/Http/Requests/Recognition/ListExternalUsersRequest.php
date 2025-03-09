<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Http\Requests\BaseRequest;

/**
 * ListExternalUsersRequest is the form request that handles the validation of the listUsers request to AWS Rekognition.
 *
 * @property string $aws_collection_id
 * @property int|null $max_results
 * @property string|null $next_token
 *
 * @class ListExternalUsersRequest
 */
final class ListExternalUsersRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'aws_collection_id' => ['required', 'string', 'exists:aws_collections,id'],
            'max_results' => ['nullable', 'integer'],
            'next_token' => ['nullable', 'string'],
        ];
    }
}
