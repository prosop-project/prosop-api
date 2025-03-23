<?php

declare(strict_types=1);

namespace App\Http\Requests\Analysis;

use App\Http\Requests\BaseRequest;
use App\Models\AnalysisOperation;
use App\Models\User;

/**
 * DeleteAnalysisOperationRequest is the form request that handles the validation of the delete analysis operation request.
 *
 * @class DeleteAnalysisOperationRequest
 */
final class DeleteAnalysisOperationRequest extends BaseRequest
{
    private User $user;

    /**
     * {@inheritDoc}
     */
    public function authorize(): bool
    {
        $userId = $this->user->id;
        $analysisOperationId = (int) $this->route('analysis_operation_id');

        // Retrieve the analysis operation, and aws similarity result from the route parameters
        $analysisOperation = AnalysisOperation::query()->findOrFail($analysisOperationId);

        /*
         * If the user is the owner of the analysis operation or an admin, they can delete the analysis operation.
         */
        return ($userId === $analysisOperation->user_id) || is_admin();
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return []; // No validation needed for deletion
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation(): void
    {
        // Fetch the user from the public uuid
        $this->user = User::query()->where('public_uuid', $this->route('public_uuid'))->firstOrFail();

        // Merge the user id into the request
        $this->merge([
            'user_id' => $this->user->id
        ]);
    }
}
