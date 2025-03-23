<?php

declare(strict_types=1);

namespace App\Services\Recognition;

use App\Enums\AnalysisOperationName;
use App\Events\ProcessFaceEvent;
use App\Events\SearchUsersByImageEvent;
use App\Models\AwsCollection;
use App\Models\AwsFace;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MoeMizrak\Rekognition\Data\AssociateFacesData;
use MoeMizrak\Rekognition\Data\CreateCollectionData;
use MoeMizrak\Rekognition\Data\DeleteCollectionData;
use MoeMizrak\Rekognition\Data\DeleteFacesData;
use MoeMizrak\Rekognition\Data\ImageData;
use MoeMizrak\Rekognition\Data\IndexFacesData;
use MoeMizrak\Rekognition\Data\ListCollectionsData;
use MoeMizrak\Rekognition\Data\ListFacesData;
use MoeMizrak\Rekognition\Data\ListUsersData;
use MoeMizrak\Rekognition\Data\ResultData\AssociateFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\CreateCollectionResultData;
use MoeMizrak\Rekognition\Data\ResultData\DeleteCollectionResultData;
use MoeMizrak\Rekognition\Data\ResultData\DeleteFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\IndexFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListCollectionsResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListUsersResultData;
use MoeMizrak\Rekognition\Data\ResultData\SearchUsersByImageResultData;
use MoeMizrak\Rekognition\Data\ResultData\UserResultData;
use MoeMizrak\Rekognition\Data\SearchUsersByImageData;
use MoeMizrak\Rekognition\Data\UserData;
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
        // Extract the required values
        $externalCollectionId = Arr::get($validatedRequest, 'external_collection_id');
        $tags = Arr::get($validatedRequest, 'tags');

        // Prepare the data to create a collection in AWS Rekognition.
        $createCollectionData = new CreateCollectionData(
            collectionId: $externalCollectionId,
            tags: $tags,
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
        // Extract the required values
        $validatedMaxResults = Arr::get($validatedRequest, 'max_results');
        $maxResults = ! is_null($validatedMaxResults) ? (int) $validatedMaxResults : null;
        $nextToken = Arr::get($validatedRequest, 'next_token');

        // Prepare the data to list collections in AWS Rekognition.
        $listCollectionsData = new ListCollectionsData(
            maxResults: $maxResults,
            nextToken: $nextToken,
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
        // Prepare the data to delete a collection in AWS Rekognition.
        $deleteCollectionData = new DeleteCollectionData(
            collectionId: $externalCollectionId,
        );

        return Rekognition::deleteCollection($deleteCollectionData);
    }

    /**
     * Create a user in AWS Rekognition (external user).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return UserResultData
     */
    public function createUser(array $validatedRequest): UserResultData
    {
        // Extract the required values
        $awsCollectionId = Arr::get($validatedRequest, 'aws_collection_id');
        $externalUserId = Arr::get($validatedRequest, 'external_user_id'); // external_user_id is set as reference_prefix-user_id in CreateOrDeleteAwsUserRequest
        $clientRequestToken = Arr::get($validatedRequest, 'client_request_token');

        // Fetch the AWS collection
        $awsCollection = AwsCollection::query()->where('id', $awsCollectionId)->firstOrFail();

        // Prepare the data to create a user in AWS Rekognition.
        $createUserData = new UserData(
            collectionId: $awsCollection->external_collection_id,
            userId: $externalUserId,
            clientRequestToken: $clientRequestToken,
        );

        return Rekognition::createUser($createUserData);
    }

    /**
     * Delete a user in AWS Rekognition (external user).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return UserResultData
     */
    public function deleteUser(array $validatedRequest): UserResultData
    {
        // Extract the required values
        $awsCollectionId = Arr::get($validatedRequest, 'aws_collection_id');
        $externalUserId = Arr::get($validatedRequest, 'external_user_id'); // external_user_id is set as reference_prefix-user_id in CreateOrDeleteAwsUserRequest
        $clientRequestToken = Arr::get($validatedRequest, 'client_request_token');

        // Fetch the AWS collection
        $awsCollection = AwsCollection::query()->where('id', $awsCollectionId)->firstOrFail();

        // Prepare the data to delete a user in AWS Rekognition.
        $deleteUserData = new UserData(
            collectionId: $awsCollection->external_collection_id,
            userId: $externalUserId,
            clientRequestToken: $clientRequestToken,
        );

        return Rekognition::deleteUser($deleteUserData);
    }

    /**
     * List users on the aws side (external users).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return ListUsersResultData
     */
    public function listExternalAwsUsers(array $validatedRequest): ListUsersResultData
    {
        // Extract the required values
        $awsCollectionId = Arr::get($validatedRequest, 'aws_collection_id');
        $validatedMaxResults = Arr::get($validatedRequest, 'max_results');
        $maxResults = ! is_null($validatedMaxResults) ? (int) $validatedMaxResults : null;
        $nextToken = Arr::get($validatedRequest, 'next_token');

        // Fetch the AWS collection
        $awsCollection = AwsCollection::query()->where('id', $awsCollectionId)->firstOrFail();

        // Prepare the data to list users in AWS Rekognition.
        $listUsersData = new ListUsersData(
            collectionId: $awsCollection->external_collection_id,
            maxResults: $maxResults,
            nextToken: $nextToken,
        );

        return Rekognition::listUsers($listUsersData);
    }

    /**
     * Index faces in AWS Rekognition.
     *
     * @param string $externalCollectionId
     * @param ImageData $imageData
     * @param string|null $externalImageId
     *
     * @return IndexFacesResultData
     */
    public function indexFaces(string $externalCollectionId, ImageData $imageData, ?string $externalImageId): IndexFacesResultData
    {
        // Prepare the data to index faces in AWS Rekognition.
        $indexFacesData = new IndexFacesData(
            collectionId: $externalCollectionId,
            image: $imageData,
            // We only index one face per image
            maxFaces: 1,
            // Generates a unique external image id by combining reference_prefix, the user id and the AWS Rekognition region and extra components
            externalImageId: $externalImageId,
            detectionAttributes: [],
        );

        return Rekognition::indexFaces($indexFacesData);
    }

    /**
     * Associate faces in AWS Rekognition.
     *
     * @param string $externalCollectionId
     * @param array<int, string> $externalFaceIds
     * @param string $externalUserId
     *
     * @return AssociateFacesResultData
     */
    public function associateFaces(
        string $externalCollectionId,
        array $externalFaceIds,
        string $externalUserId
    ): AssociateFacesResultData {
        // Prepare the data to associate faces in AWS Rekognition.
        $associateFacesData = new AssociateFacesData(
            collectionId: $externalCollectionId,
            faceIds: $externalFaceIds,
            userId: $externalUserId,
            userMatchThreshold: (float) config('aws-rekognition.user_match_threshold'),
        );

        return Rekognition::associateFaces($associateFacesData);
    }

    /**
     * Process faces where respectively it calls indexFacesJob and associateFacesJob for the full cycle of face processing.
     *
     * @param array<string, mixed> $validatedRequest
     * @param User $user
     *
     * @return void
     */
    public function processFaces(array $validatedRequest, User $user): void
    {
        // Extract aws collection id
        $awsCollectionId = Arr::get($validatedRequest, 'aws_collection_id');

        // Extract images
        $images = Arr::get($validatedRequest, 'images');

        $tempImagePaths = [];

        // Loop through the images and store them in a temporary path
        foreach ($images as $image) {
            // Generate a temporary path for the image
            $tempPath = 'temp/' . Str::uuid() . '.' . $image->getClientOriginalExtension();

            // Store the image in the temporary path
            Storage::put($tempPath, $image->getContent());

            // Add the temporary path to the array
            $tempImagePaths[] = $tempPath;
        }

        event(new ProcessFaceEvent($awsCollectionId, $tempImagePaths, $user));
    }

    /**
     * List external faces on the AWS side (external faces).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return ListFacesResultData
     */
    public function listExternalFaces(array $validatedRequest): ListFacesResultData
    {
        // Retrieve the AWS collection id from the request
        $awsCollectionId = Arr::get($validatedRequest, 'aws_collection_id');
        $awsCollection = AwsCollection::query()->where('id', $awsCollectionId)->firstOrFail(); // Fetch the AWS collection
        $externalCollectionId = $awsCollection->external_collection_id; // On the AWS side, the collection id refers to the external_collection_id on our side

        // Retrieve the user id from the request
        $userId = Arr::get($validatedRequest, 'user_id');
        // On the AWS side, the user id refers to the external_user_id on our side
        $externalUserId = ! is_null($userId) ? generate_external_id((int) $userId) : null;

        // Retrieve the aws face ids, and get the external face ids from the database
        $awsFaceIds = Arr::get($validatedRequest, 'aws_face_ids', []);
        $externalFaceIds = AwsFace::query()
            ->whereIn('id', $awsFaceIds)
            ->pluck('external_face_id')
            ->toArray();

        // Retrieve the max results and next token from the request
        $validatedMaxResults = Arr::get($validatedRequest, 'max_results');
        $maxResults = ! is_null($validatedMaxResults) ? (int) $validatedMaxResults : null;
        $nextToken = Arr::get($validatedRequest, 'next_token');

        // Prepare the data to list faces in AWS Rekognition
        $listFacesData = new ListFacesData(
            collectionId: $externalCollectionId,
            userId: $externalUserId,
            faceIds: $externalFaceIds,
            maxResults: $maxResults,
            nextToken: $nextToken,
        );

        return Rekognition::listFaces($listFacesData);
    }

    /**
     * Delete faces from a collection in AWS Rekognition.
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return DeleteFacesResultData
     */
    public function deleteFaces(array $validatedRequest): DeleteFacesResultData
    {
        // Retrieve the AWS collection id from the request
        $awsCollectionId = Arr::get($validatedRequest, 'aws_collection_id');
        $awsCollection = AwsCollection::query()->where('id', $awsCollectionId)->firstOrFail(); // Fetch the AWS collection
        $externalCollectionId = $awsCollection->external_collection_id; // On the AWS side, the collection id refers to the external_collection_id on our side

        // Retrieve the aws face ids, and get the external face ids from the database
        $awsFaceIds = Arr::get($validatedRequest, 'aws_face_ids', []);
        $externalFaceIds = AwsFace::query()
            ->whereIn('id', $awsFaceIds)
            ->pluck('external_face_id')
            ->toArray();

        // Prepare the data to delete faces in AWS Rekognition
        $deleteFacesData = new DeleteFacesData(
            collectionId: $externalCollectionId,
            faceIds: $externalFaceIds,
        );

        return Rekognition::deleteFaces($deleteFacesData);
    }

    /**
     * This method handles the search users by image, and other searches that can be added such as search faces by image and so on.
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return void
     */
    public function search(array $validatedRequest): void
    {
        // Extract the required values.
        $publicUuid = Arr::get($validatedRequest, 'public_uuid');
        $awsCollectionId = Arr::get($validatedRequest, 'aws_collection_id');
        $image = Arr::get($validatedRequest, 'image');
        $maxUsers = Arr::get($validatedRequest, 'max_users');
        $analysisOperations = Arr::get($validatedRequest, 'analysis_operations');

        // Fetch the user from the public uuid, user id will be used internally.
        $userId = User::query()->where('public_uuid', $publicUuid)->firstOrFail(['id'])->id;

        // Here we can control which event to fire based on the request. For now, we only have search users by image.
        if (in_array(AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value, $analysisOperations, true)) {
            // Generate a temporary path for the image
            $tempImagePath = 'temp/' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            // Store the image in the temporary path
            Storage::put($tempImagePath, $image->getContent());

            event(new SearchUsersByImageEvent($userId, $awsCollectionId, $tempImagePath, $maxUsers));
        }
    }

    /**
     * Search users by image in AWS Rekognition.
     *
     * @param string $externalCollectionId
     * @param ImageData $imageData
     * @param int|null $maxUsers
     *
     * @return SearchUsersByImageResultData
     */
    public function searchUsersByImage(
        string $externalCollectionId,
        ImageData $imageData,
        ?int $maxUsers = null
    ): SearchUsersByImageResultData {
        // Prepare the data to search users by image in AWS Rekognition.
        $searchUsersByImageData = new SearchUsersByImageData(
            collectionId: $externalCollectionId,
            image: $imageData,
            maxUsers: $maxUsers ?? (int) config('aws-rekognition.search_result_max_users'),
            userMatchThreshold: (float) config('aws-rekognition.user_match_threshold'),
        );

        return Rekognition::searchUsersByImage($searchUsersByImageData);
    }
}
