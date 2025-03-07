<?php

declare(strict_types=1);

namespace App\Services\Recognition;

use Illuminate\Support\Arr;
use MoeMizrak\Rekognition\Data\CreateCollectionData;
use MoeMizrak\Rekognition\Data\DeleteCollectionData;
use MoeMizrak\Rekognition\Data\ListCollectionsData;
use MoeMizrak\Rekognition\Data\ResultData\CreateCollectionResultData;
use MoeMizrak\Rekognition\Data\ResultData\DeleteCollectionResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListCollectionsResultData;
use MoeMizrak\Rekognition\Facades\Rekognition;

/**
 * AwsRekognitionService is a service class that handles the logic of AWS Rekognition requests.
 *
 * @class AwsRekognitionService
 */
final readonly class AwsRekognitionService
{
    /**
     * Create a collection in AWS Rekognition (external collection).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return CreateCollectionResultData
     */
    public function createCollection(array $validatedRequest): CreateCollectionResultData
    {
        // Prepare the data to create a collection in AWS Rekognition.
        $createCollectionData = new CreateCollectionData(
            collectionId: Arr::get($validatedRequest, 'collection_id'),
            tags: Arr::get($validatedRequest, 'tags'),
        );

        return Rekognition::createCollection($createCollectionData);
    }

    /**
     * List collections on the aws side (external collections).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return ListCollectionsResultData
     */
    public function listExternalCollections(array $validatedRequest): ListCollectionsResultData
    {
        $listCollectionsData = new ListCollectionsData(
            maxResults: Arr::get($validatedRequest, 'max_results'),
            nextToken: Arr::get($validatedRequest, 'next_token'),
        );

        return Rekognition::listCollections($listCollectionsData);
    }

    /**
     * Delete a collection in AWS Rekognition (external collection).
     *
     * @param string $externalCollectionId
     *
     * @return DeleteCollectionResultData
     */
    public function deleteCollection(string $externalCollectionId): DeleteCollectionResultData
    {
        $deleteCollectionData = new DeleteCollectionData(
            collectionId: $externalCollectionId,
        );

        return Rekognition::deleteCollection($deleteCollectionData);
    }
}
