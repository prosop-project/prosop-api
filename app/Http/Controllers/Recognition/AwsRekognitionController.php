<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recognition;

use App\Actions\Recognition\CreateAwsUserAction;
use App\Actions\Recognition\CreateCollectionAction;
use App\Actions\Recognition\DeleteAwsUserAction;
use App\Actions\Recognition\DeleteCollectionAction;
use App\Actions\Recognition\DeleteFacesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recognition\CreateCollectionRequest;
use App\Http\Requests\Recognition\CreateOrDeleteAwsUserRequest;
use App\Http\Requests\Recognition\DeleteFacesRequest;
use App\Http\Requests\Recognition\ListExternalCollectionsRequest;
use App\Http\Requests\Recognition\ListExternalFacesRequest;
use App\Http\Requests\Recognition\ListExternalUsersRequest;
use App\Http\Requests\Recognition\ProcessFacesRequest;
use App\Http\Requests\Recognition\SearchCollectionRequest;
use App\Http\Resources\AwsCollectionResource;
use App\Http\Resources\AwsFaceResource;
use App\Http\Resources\AwsUserResource;
use App\Http\Resources\GenericResponseResource;
use App\Http\Resources\ListExternalCollectionResource;
use App\Http\Resources\ListExternalFacesResource;
use App\Http\Resources\ListExternalUsersResource;
use App\Models\AwsCollection;
use App\Models\AwsFace;
use App\Models\AwsUser;
use App\Models\User;
use App\Services\Recognition\AwsRekognitionInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @class AwsRekognitionController
 */
final readonly class AwsRekognitionController extends Controller
{
    /**
     * @param AwsRekognitionInterface $awsRekognitionService
     */
    public function __construct(protected AwsRekognitionInterface $awsRekognitionService) {}

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
        // Retrieve the client request token from the request.
        $clientRequestToken = $request->client_request_token;

        // Find the user in the aws_users table.
        $awsUser = AwsUser::query()
            ->where('aws_collection_id', $request->aws_collection_id)
            ->where('external_user_id', $request->external_user_id)
            ->firstOrFail();

        // Delete the existing user in the aws_users table.
        $deleteAwsUserAction->handle($awsUser, $clientRequestToken);

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

    /**
     * Get all faces stored in the database (aws_faces table).
     *
     * @return AnonymousResourceCollection
     */
    public function getAwsFaces(): AnonymousResourceCollection
    {
        return AwsFaceResource::collection(AwsFace::all());
    }

    /**
     * List external faces on the AWS side.
     *
     * @param ListExternalFacesRequest $request
     *
     * @return ListExternalFacesResource
     */
    public function listExternalFaces(ListExternalFacesRequest $request): ListExternalFacesResource
    {
        // List external faces on the aws side (external faces).
        $faces = $this->awsRekognitionService->listExternalFaces($request->validated());

        return new ListExternalFacesResource($faces);
    }

    /**
     * Delete faces from both the database and AWS Rekognition.
     *
     * @param DeleteFacesRequest $request
     * @param DeleteFacesAction $deleteFacesAction
     *
     * @return GenericResponseResource
     */
    public function deleteFaces(DeleteFacesRequest $request, DeleteFacesAction $deleteFacesAction): GenericResponseResource
    {
        // Set the collection and face ids.
        $groupedAwsFaces = [
            [
                'aws_collection_id' => $request->aws_collection_id,
                'aws_face_ids' => $request->aws_face_ids,
            ],
        ];

        // Delete the existing user in the aws_users table.
        $deleteFacesAction->handle($groupedAwsFaces);

        return new GenericResponseResource('Faces are deleted from both database and aws side successfully!');
    }

    /**
     * Search collection for matching faces, user ids and so on.
     *
     * @param SearchCollectionRequest $request
     *
     * @return GenericResponseResource
     */
    public function searchCollection(SearchCollectionRequest $request): GenericResponseResource
    {
        // Search collection for matching faces, user ids and so on.
        $this->awsRekognitionService->search($request->validated());

        return new GenericResponseResource('Search collection request is sent successfully!');
    }
}
