<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Http\Requests\BaseRequest;

/**
 * CreateCollectionsRequest is the form request that handles the validation of the create collection request to AWS Rekognition.
 *
 * @property string $collection_id
 * @property array<string, mixed>|null $tags
 *
 * @class CreateCollectionsRequest
 */
final class CreateCollectionsRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'collection_id' => ['required', 'string'],
            'tags' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
