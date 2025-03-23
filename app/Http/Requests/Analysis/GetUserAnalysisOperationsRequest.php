<?php

declare(strict_types=1);

namespace App\Http\Requests\Analysis;

use App\Enums\AnalysisOperationName;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * GetUserAnalysisOperationsRequest is the form request that handles the validation of the get user analysis operations request.
 *
 * @property string $public_uuid
 * @property int|null $aws_collection_id
 * @property string|null $operation
 * @property string|null $status
 *
 * @class GetUserAnalysisOperationsRequest
 */
final class GetUserAnalysisOperationsRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'public_uuid' => ['required', 'string', 'exists:users,public_uuid'],
            'aws_collection_id' => ['nullable', 'int', 'exists:aws_collections,id'],
            'operation' => [
                'nullable',
                'string',
                Rule::in(array_column(AnalysisOperationName::cases(), 'value')),
            ],
            'status' => ['nullable', 'string'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation(): void
    {
        // Merge the public_uuid from the route parameters, ensuring it is included in the request retrieved from route parameters
        $this->merge([
            'public_uuid' => $this->route('public_uuid'),
        ]);
    }
}
