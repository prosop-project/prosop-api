<?php

declare(strict_types=1);

namespace App\Services\Recognition;

use App\Events\ProcessFaceEvent;
use App\Models\AwsCollection;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use MoeMizrak\Rekognition\Data\AssociateFacesData;
use MoeMizrak\Rekognition\Data\CreateCollectionData;
use MoeMizrak\Rekognition\Data\DeleteCollectionData;
use MoeMizrak\Rekognition\Data\ImageData;
use MoeMizrak\Rekognition\Data\IndexFacesData;
use MoeMizrak\Rekognition\Data\ListCollectionsData;
use MoeMizrak\Rekognition\Data\ListUsersData;
use MoeMizrak\Rekognition\Data\ResultData\AssociateFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\CreateCollectionResultData;
use MoeMizrak\Rekognition\Data\ResultData\DeleteCollectionResultData;
use MoeMizrak\Rekognition\Data\ResultData\IndexFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListCollectionsResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListUsersResultData;
use MoeMizrak\Rekognition\Data\ResultData\UserResultData;
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
        $maxResults = Arr::get($validatedRequest, 'max_results');
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
        $maxResults = Arr::get($validatedRequest, 'max_results');
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
     * @param UploadedFile $image
     * @param User $user
     *
     * @return IndexFacesResultData
     */
    public function indexFaces(string $externalCollectionId, UploadedFile $image, User $user): IndexFacesResultData
    {
        // Read the image bytes (base64 encoded).
        $base64Image = base64_encode($image->getContent());

        // Prepare the image data.
        $imageData = new ImageData(
            bytes: $base64Image,
        );

        // Extract the image extension and size in KB for the external image id.
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->getMimeType());
        $imageSizeKb = (int) round($image->getSize() / 1024);

        // Prepare the data to index faces in AWS Rekognition.
        $indexFacesData = new IndexFacesData(
            collectionId: $externalCollectionId,
            image: $imageData,
            // We only index one face per image
            maxFaces: 1,
            // Generates a unique external image id by combining reference_prefix, the user id and the AWS Rekognition region and extra components
            externalImageId: generate_external_id(userId: $user->id, includeRegion: true, extraComponents: [$extension, $imageSizeKb]),
            qualityFilter: 'AUTO',
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
        // Extract the required values
        $awsCollectionId = Arr::get($validatedRequest, 'aws_collection_id');
        $images = Arr::get($validatedRequest, 'images');

        event(new ProcessFaceEvent($awsCollectionId, $images, $user));
    }
}
