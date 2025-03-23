<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Http\Requests\BaseRequest;

/**
 * CreateCollectionRequest is the form request that handles the validation of the create collection request to AWS Rekognition.
 *
 * @property string $external_collection_id
 * @property array<string, mixed>|null $tags
 *
 * @class CreateCollectionRequest
 */
final class CreateCollectionRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'external_collection_id' => ['required', 'string'],
            'tags' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
