<?php

declare(strict_types=1);

namespace App\Http\Controllers\Analysis;

use App\Actions\Analysis\DeleteAnalysisOperationAction;
use App\Actions\Analysis\DeleteAwsSimilarityResultAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Analysis\DeleteAnalysisOperationRequest;
use App\Http\Requests\Analysis\DeleteAwsSimilarityResultRequest;
use App\Http\Requests\Analysis\GetUserAnalysisOperationsRequest;
use App\Http\Resources\GenericResponseResource;
use App\Http\Resources\UserAnalysisOperationsResource;
use App\Models\AnalysisOperation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @class AnalysisController
 */
final readonly class AnalysisController extends Controller
{
    /**
     * Get all analysis operations for the given user.
     *
     * @param GetUserAnalysisOperationsRequest $request
     * @param int $userId
     *
     * @return AnonymousResourceCollection
     */
    public function getUserAnalysisOperations(GetUserAnalysisOperationsRequest $request, int $userId): AnonymousResourceCollection
    {
        // Query the analysis operations for the given user including the aws similarity results where aws user and user are eager loaded
        $query = AnalysisOperation::query()
            ->where('user_id', $userId)
            ->with(['awsSimilarityResults.awsUser.user']);

        // Filter by aws collection id if provided
        if ($request->filled('aws_collection_id')) {
            $query->where('aws_collection_id', $request->query('aws_collection_id'));
        }

        // Filter by operation type if provided
        if ($request->filled('operation')) {
            $query->where('operation', $request->query('operation'));
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Get the user analysis operations
        $userAnalysisOperations = $query->get();

        return UserAnalysisOperationsResource::collection($userAnalysisOperations);
    }

    /**
     * Delete an analysis operation along with its aws similarity results.
     *
     * @param DeleteAnalysisOperationRequest $_
     * @param int $userId
     * @param int $analysisOperationId
     * @param DeleteAnalysisOperationAction $deleteAnalysisOperationAction
     *
     * @return GenericResponseResource
     */
    public function deleteAnalysisOperation(
        DeleteAnalysisOperationRequest $_,
        int $userId,
        int $analysisOperationId,
        DeleteAnalysisOperationAction $deleteAnalysisOperationAction
    ): GenericResponseResource {
        $deleteAnalysisOperationAction->handle($analysisOperationId);

        return new GenericResponseResource('Analysis operation is deleted successfully!');
    }

    /**
     * Delete an aws similarity result.
     *
     * @param DeleteAwsSimilarityResultRequest $_
     * @param int $userId
     * @param int $analysisOperationId
     * @param int $similarityResultId
     * @param DeleteAwsSimilarityResultAction $deleteAwsSimilarityResultAction
     *
     * @return GenericResponseResource
     */
    public function deleteAwsSimilarityResult(
        DeleteAwsSimilarityResultRequest $_,
        int $userId,
        int $analysisOperationId,
        int $similarityResultId,
        DeleteAwsSimilarityResultAction $deleteAwsSimilarityResultAction
    ): GenericResponseResource {
        $deleteAwsSimilarityResultAction->handle($similarityResultId);

        return new GenericResponseResource('Aws similarity result is deleted successfully!');
    }
}
