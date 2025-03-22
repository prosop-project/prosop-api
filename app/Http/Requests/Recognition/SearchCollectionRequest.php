<?php

declare(strict_types=1);

namespace App\Http\Requests\Recognition;

use App\Enums\AnalysisOperationName;
use App\Http\Requests\BaseRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * SearchCollectionRequest is the form request that handles the validation of the searchUsersByImage request,
 * and other searches that may be added in the future.
 *
 * @property int $user_id
 * @property int $aws_collection_id
 * @property array<int, string> $analysis_operations
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
            'user_id' => ['required', 'int', 'exists:users,id'],
            'aws_collection_id' => ['required', 'int', 'exists:aws_collections,id'],
            'analysis_operations' => ['required', 'array'],
            'analysis_operations.*' => [
                'required',
                'string',
                Rule::in(array_column(AnalysisOperationName::cases(), 'value'))
            ],
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,heic,heif,webp',
                'max:10240',
            ],
            'max_users' => ['sometimes', 'nullable', 'int', 'min:1', 'max:20'],
        ];
    }
}
