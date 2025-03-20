<?php

declare(strict_types=1);

namespace App\Actions\Analysis;

use App\Models\AwsSimilarityResult;

/**
 * @class CreateAwsSimilarityResultAction
 */
final readonly class CreateAwsSimilarityResultAction
{
    /**
     * Handle the action.
     *
     * @param int $analysisRequestId
     * @param float $similarity
     * @param int|null $awsUserId
     * @param array<string, mixed>|null $metadata
     *
     * @return AwsSimilarityResult
     */
    public function handle(
        int $analysisRequestId,
        float $similarity,
        ?int $awsUserId = null,
        ?array $metadata = null
    ): AwsSimilarityResult {
        // Create a new aws similarity result record in the database (aws_similarity_results table).
        return AwsSimilarityResult::query()->create([
            'analysis_request_id' => $analysisRequestId,
            'aws_user_id' => $awsUserId,
            'similarity' => $similarity,
            'metadata' => $metadata,
        ]);
    }
}
