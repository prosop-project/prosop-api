<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Http\Requests\BaseRequest;
use Illuminate\Http\UploadedFile;

/**
 * ProcessFacesRequest is the form request that handles the validation of the process_faces/{user} request.
 *
 * @property int $aws_collection_id
 * @property array<int, UploadedFile> $images
 *
 * @class ProcessFacesRequest
 */
final class ProcessFacesRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): bool
    {
        return (auth()->id() === $this->route('user')->id)
            || is_admin();
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'aws_collection_id' => ['required', 'int', 'exists:aws_collections,id'],
            'images' => ['required', 'array', 'min:1', 'max:' . config('aws-rekognition.max_faces_per_user')],
            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,heic,heif,webp',
                'max:10240',
            ],
        ];
    }
}
