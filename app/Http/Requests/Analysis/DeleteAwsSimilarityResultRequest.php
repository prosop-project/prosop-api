<?php

declare(strict_types=1);

namespace App\Http\Requests\Analysis;

use App\Http\Requests\BaseRequest;
use App\Models\AnalysisOperation;
use App\Models\AwsSimilarityResult;
use App\Models\User;

/**
 * DeleteAwsSimilarityResultRequest is the form request that handles the validation of the delete aws similarity result request.
 *
 * @class DeleteAwsSimilarityResultRequest
 */
final class DeleteAwsSimilarityResultRequest extends BaseRequest
{
    private User $user;

    /**
     * {@inheritDoc}
     */
    public function authorize(): bool
    {
        $userId = $this->user->id;
        $analysisOperationId = (int) $this->route('analysis_operation_id');
        $awsSimilarityResultId = (int) $this->route('aws_similarity_result_id');

        // Retrieve the analysis operation, and aws similarity result from the route parameters
        $analysisOperation = AnalysisOperation::query()->findOrFail($analysisOperationId);
        $awsSimilarityResult = AwsSimilarityResult::query()->findOrFail($awsSimilarityResultId);

        /*
         * If the user is the owner of the analysis operation, and the analysis operation is the parent of the aws similarity result, they can delete the aws similarity result.
         * If the user is an admin, they can delete the aws similarity result, regardless of the owner.
         */
        return (
            $userId === $analysisOperation->user_id
            && $analysisOperationId === $awsSimilarityResult->analysis_operation_id
        ) || is_admin();
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
