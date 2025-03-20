<?php

declare(strict_types=1);

namespace App\Actions\Analysis;

use App\Enums\Status;
use App\Models\AnalysisRequest;

/**
 * @class CreateAnalysisRequestAction
 */
final readonly class CreateAnalysisRequestAction
{
    /**
     * Handle the action.
     *
     * @param int $userId
     * @param int $awsCollectionId
     * @param string $operation
     * @param array<string, mixed>|null $metadata
     *
     * @return AnalysisRequest
     */
    public function handle(
        int $userId,
        int $awsCollectionId,
        string $operation,
        ?array $metadata = null
    ): AnalysisRequest {
        // Create a new analysis request.
        return AnalysisRequest::query()->create([
            'user_id' => $userId,
            'aws_collection_id' => $awsCollectionId,
            'operation' => $operation,
            'status' => Status::PENDING->value,
            'metadata' => $metadata,
        ]);
    }
}
