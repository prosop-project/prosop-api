<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Http\Requests\BaseRequest;

/**
 * ListExternalCollectionsRequest is the form request that handles the validation of the listCollections request to AWS Rekognition.
 *
 * @property int|null $max_results
 * @property string|null $next_token
 *
 * @class ListExternalCollectionsRequest
 */
final class ListExternalCollectionsRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'max_results' => ['nullable', 'integer'],
            'next_token' => ['nullable', 'string'],
        ];
    }
}
