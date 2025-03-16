<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Http\Requests\BaseRequest;
use Illuminate\Http\UploadedFile;

/**
 * SearchCollectionRequest is the form request that handles the validation of the searchUsersByImage request,
 * and other searches that may be added in the future.
 *
 * @property int $aws_collection_id
 * @property UploadedFile $image
 * @property int|null $max_users
 *
 * @class SearchCollectionRequest
 */
final class SearchCollectionRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'search_strategies' => ['required', 'array'],
            'search_strategies.*' => ['required', 'string', 'in:search_users_by_image'],
            'aws_collection_id' => ['required', 'int', 'exists:aws_collections,id'],
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,heic,heif,webp',
                'max:10240',
            ],
            'max_users' => ['nullable', 'int', 'min:1', 'max:20'],
        ];
    }
}
