<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recognition;

use App\Actions\Recognition\CreateCollectionAction;
use App\Actions\Recognition\DeleteCollectionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recognition\CreateCollectionsRequest;
use App\Http\Requests\Recognition\ListExternalCollectionsRequest;
use App\Http\Resources\AwsCollectionResource;
use App\Http\Resources\GenericResponseResource;
use App\Http\Resources\ListExternalCollectionResource;
use App\Models\AwsCollection;
use App\Services\Recognition\AwsRekognitionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @class AwsRekognitionController
 */
final readonly class AwsRekognitionController extends Controller
{
    /**
     * @param AwsRekognitionService $awsRekognitionService
     */
    public function __construct(protected AwsRekognitionService $awsRekognitionService) {}

    /**
     * Create new collection in AWS Rekognition and store it in the database (aws_collections table).
     *
     * @param CreateCollectionsRequest $request
     * @param CreateCollectionAction $createCollectionAction
     *
     * @return AwsCollectionResource
     */
    public function createCollection(
        CreateCollectionsRequest $request,
        CreateCollectionAction $createCollectionAction
    ): AwsCollectionResource {
        // Create a new collection in AWS Rekognition by sending a request to AWS Rekognition API.
        $awsResponse = $this->awsRekognitionService->createCollection($request->validated());

        // Store the collection in the database by calling the action.
        $awsCollection = $createCollectionAction->handle($request, $awsResponse);

        return new AwsCollectionResource($awsCollection);
    }

    /**
     * Delete collection from both the database and AWS Rekognition.
     *
     * @param AwsCollection $collection
     * @param DeleteCollectionAction $deleteCollectionAction
     *
     * @return GenericResponseResource
     */
    public function deleteCollection(
        AwsCollection $collection,
        DeleteCollectionAction $deleteCollectionAction
    ): GenericResponseResource {
        // Delete the collection from the aws side (external collection).
        $this->awsRekognitionService->deleteCollection($collection->external_collection_id);

        // Delete the collection from the database.
        $deleteCollectionAction->handle($collection);

        return new GenericResponseResource('Aws collection is deleted from both database and aws side successfully!');
    }

    /**
     * List external collections on the AWS side.
     *
     * @param ListExternalCollectionsRequest $request
     *
     * @return ListExternalCollectionResource
     */
    public function listExternalCollections(ListExternalCollectionsRequest $request): ListExternalCollectionResource
    {
        $collections = $this->awsRekognitionService->listExternalCollections($request->validated());

        return new ListExternalCollectionResource($collections);
    }

    /**
     * Get all collections stored in the database (aws_collections table).
     *
     * @return AnonymousResourceCollection
     */
    public function getAwsCollections(): AnonymousResourceCollection
    {
        return AwsCollectionResource::collection(AwsCollection::all());
    }
}
