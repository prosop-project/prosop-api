<?php

declare(strict_types=1);

namespace App\Http\Requests\Analysis;

use App\Enums\AnalysisOperationName;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * GetUserAnalysisOperationsRequest is the form request that handles the validation of the get user analysis operations request.
 *
 * @property int $user_id
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
            'user_id' => ['required', 'int', 'exists:users,id'],
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
        // Merge the user_id from the route parameters, ensuring it is included in the request retrieved from route parameters
        $this->merge([
            'user_id' => $this->route('user_id'),
        ]);
    }
}
