<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Http\Requests\BaseRequest;

/**
 * DeleteFacesRequest is the form request that handles the validation of the deleteFaces request to AWS Rekognition.
 *
 * @property int $aws_collection_id
 * @property array<int, int> $aws_face_ids
 *
 * @class DeleteFacesRequest
 */
final class DeleteFacesRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'aws_collection_id' => ['required', 'int', 'exists:aws_collections,id'],
            'aws_face_ids' => ['required', 'array'],
            'aws_face_ids.*' => ['exists:aws_faces,id'],
        ];
    }
}
