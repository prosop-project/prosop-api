<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Http\Requests\BaseRequest;

/**
 * ListExternalFacesRequest is the form request that handles the validation of the listFaces request to AWS Rekognition.
 *
 * @property int $aws_collection_id
 * @property int|null $user_id
 * @property array<int, int>|null $aws_face_ids
 * @property int|null $max_results
 * @property string|null $next_token
 *
 * @class ListExternalFacesRequest
 */
final class ListExternalFacesRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'aws_collection_id' => ['required', 'int', 'exists:aws_collections,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'aws_face_ids' => ['nullable', 'array'],
            'aws_face_ids.*' => ['exists:aws_faces,id'],
            'max_results' => ['nullable', 'integer'],
            'next_token' => ['nullable', 'string'],
        ];
    }
}
