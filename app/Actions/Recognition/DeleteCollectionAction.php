<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Models\AwsCollection;
use App\Services\Recognition\AwsRekognitionService;

/**
 * @class DeleteCollectionAction
 */
final readonly class DeleteCollectionAction
{
    /**
     * @param AwsRekognitionService $awsRekognitionService
     */
    public function __construct(private AwsRekognitionService $awsRekognitionService) {}

    /**
     * Handle the action.
     *
     * @param AwsCollection $awsCollection
     *
     * @return void
     */
    public function handle(AwsCollection $awsCollection): void
    {
        // Delete the collection from the aws side (external collection).
        $this->awsRekognitionService->deleteCollection($awsCollection->external_collection_id);

        // Delete the collection from the database.
        $awsCollection->delete();
    }
}
