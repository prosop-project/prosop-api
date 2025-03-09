<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Http\Requests\Recognition\CreateCollectionRequest;
use App\Models\AwsCollection;
use MoeMizrak\Rekognition\Data\ResultData\CreateCollectionResultData;

/**
 * @class CreateCollectionAction
 */
final readonly class CreateCollectionAction
{
    /**
     * Handle the action.
     *
     * @param CreateCollectionRequest $request
     * @param CreateCollectionResultData $awsResponse
     *
     * @return AwsCollection
     */
    public function handle(CreateCollectionRequest $request, CreateCollectionResultData $awsResponse): AwsCollection
    {
        return AwsCollection::query()->create([
            'external_collection_id' => $request->external_collection_id,
            'external_collection_arn' => $awsResponse->collectionArn,
            'tags' => $request->tags,
            'face_model_version' => $awsResponse->faceModelVersion,
        ]);
    }
}
