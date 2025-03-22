<?php

declare(strict_types=1);

namespace App\Http\Requests\Analysis;

use App\Http\Requests\BaseRequest;
use App\Models\AnalysisOperation;

/**
 * DeleteAnalysisOperationRequest is the form request that handles the validation of the delete analysis operation request.
 *
 * @class DeleteAnalysisOperationRequest
 */
final class DeleteAnalysisOperationRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): bool
    {
        $userId = (int) $this->route('user_id');
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
}
