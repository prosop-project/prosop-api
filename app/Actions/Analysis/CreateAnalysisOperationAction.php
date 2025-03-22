<?php

declare(strict_types=1);

namespace App\Actions\Analysis;

use App\Enums\Status;
use App\Models\AnalysisOperation;

/**
 * @class CreateAnalysisOperationAction
 */
final readonly class CreateAnalysisOperationAction
{
    /**
     * Handle the action.
     *
     * @param int $userId
     * @param int $awsCollectionId
     * @param string $operation
     * @param array<string, mixed>|null $metadata
     *
     * @return AnalysisOperation
     */
    public function handle(
        int $userId,
        int $awsCollectionId,
        string $operation,
        ?array $metadata = null
    ): AnalysisOperation {
        // Create a new analysis operation.
        return AnalysisOperation::query()->create([
            'user_id' => $userId,
            'aws_collection_id' => $awsCollectionId,
            'operation' => $operation,
            'status' => Status::PENDING->value,
            'metadata' => $metadata,
        ]);
    }
}
