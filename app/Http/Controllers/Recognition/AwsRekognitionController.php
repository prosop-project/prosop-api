<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recognition;

use App\Actions\Recognition\CreateAwsUserAction;
use App\Actions\Recognition\CreateCollectionAction;
use App\Actions\Recognition\DeleteAwsUserAction;
use App\Actions\Recognition\DeleteCollectionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recognition\CreateCollectionRequest;
use App\Http\Requests\Recognition\CreateOrDeleteAwsUserRequest;
use App\Http\Requests\Recognition\ListExternalCollectionsRequest;
use App\Http\Requests\Recognition\ListExternalUsersRequest;
use App\Http\Requests\Recognition\ProcessFacesRequest;
use App\Http\Resources\AwsCollectionResource;
use App\Http\Resources\AwsUserResource;
use App\Http\Resources\GenericResponseResource;
use App\Http\Resources\ListExternalCollectionResource;
use App\Http\Resources\ListExternalUsersResource;
use App\Models\AwsCollection;
use App\Models\AwsUser;
use App\Models\User;
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
     * @param CreateCollectionRequest $request
     * @param CreateCollectionAction $createCollectionAction
     *
     * @return AwsCollectionResource
     */
    public function createCollection(
        CreateCollectionRequest $request,
        CreateCollectionAction $createCollectionAction
    ): AwsCollectionResource {
        // Create a new collection in AWS Rekognition by sending a request to AWS Rekognition API.
        $awsResponse = $this->awsRekognitionService->createCollection($request->validated());

        // Store the collection in the database by calling the action (aws_collections table).
        $awsCollection = $createCollectionAction->handle($request, $awsResponse);

        return new AwsCollectionResource($awsCollection);
    }

    /**
     * Delete collection from both the database and AWS Rekognition.
     *
     * @param AwsCollection $awsCollection
     * @param DeleteCollectionAction $deleteCollectionAction
     *
     * @return GenericResponseResource
     */
    public function deleteCollection(
        AwsCollection $awsCollection,
        DeleteCollectionAction $deleteCollectionAction
    ): GenericResponseResource {
        // Delete the collection from the aws side (external collection).
        $this->awsRekognitionService->deleteCollection($awsCollection->external_collection_id);

        // Delete the collection from the database.
        $deleteCollectionAction->handle($awsCollection);

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
        // List external collections on the aws side (external collections).
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

    /**
     * Create a new user (with external_user_id) in AWS Rekognition in specified collection and store it in the database (aws_users table).
     *
     * @param CreateOrDeleteAwsUserRequest $request
     * @param CreateAwsUserAction $createAwsUserAction
     *
     * @return AwsUserResource
     */
    public function createAwsUser(
        CreateOrDeleteAwsUserRequest $request,
        CreateAwsUserAction $createAwsUserAction
    ): AwsUserResource {
        // Create a new user on AWS Rekognition side by sending a request to AWS Rekognition API.
        $this->awsRekognitionService->createUser($request->all());

        // Store the created user to the aws_users table.
        $awsUser = $createAwsUserAction->handle($request);

        return new AwsUserResource($awsUser);
    }

    /**
     * Delete aws user from both the database and AWS Rekognition.
     *
     * @param CreateOrDeleteAwsUserRequest $request
     * @param DeleteAwsUserAction $deleteAwsUserAction
     *
     * @return GenericResponseResource
     */
    public function deleteAwsUser(
        CreateOrDeleteAwsUserRequest $request,
        DeleteAwsUserAction $deleteAwsUserAction
    ): GenericResponseResource {
        // Delete a user on AWS Rekognition side by sending a request to AWS Rekognition API.
        $this->awsRekognitionService->deleteUser($request->all());

        // Delete the existing user in the aws_users table.
        $deleteAwsUserAction->handle($request);

        return new GenericResponseResource('Aws user is deleted from both database and aws side successfully!');
    }

    /**
     * Get all users stored in the database (aws_users table).
     *
     * @return AnonymousResourceCollection
     */
    public function getAwsUsers(): AnonymousResourceCollection
    {
        return AwsUserResource::collection(AwsUser::all());
    }

    /**
     * List external users on the AWS side.
     *
     * @param ListExternalUsersRequest $request
     *
     * @return ListExternalUsersResource
     */
    public function listExternalAwsUsers(ListExternalUsersRequest $request): ListExternalUsersResource
    {
        // List external users on the aws side (external users).
        $users = $this->awsRekognitionService->listExternalAwsUsers($request->validated());

        return new ListExternalUsersResource($users);
    }

    /**
     * Process faces of the images (by calling respectively indexFaces and associateFaces methods) and store the results in the database.
     * Queue jobs are used for processing the faces, because the process may take a long time so we return a response immediately and notify the user later.
     *
     * @param ProcessFacesRequest $request
     * @param User $user
     *
     * @return GenericResponseResource
     */
    public function processFaces(ProcessFacesRequest $request, User $user): GenericResponseResource
    {
        $this->awsRekognitionService->processFaces($request->validated(), $user);

        return new GenericResponseResource('Process faces request is sent successfully!');
    }
}
