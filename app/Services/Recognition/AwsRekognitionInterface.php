<?php

declare(strict_types=1);

namespace App\Services\Recognition;

use App\Models\AwsUser;
use MoeMizrak\Rekognition\Data\ImageData;
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

/**
 * Interface AwsRekognitionInterface which defines the methods that are used to interact with AWS Rekognition.
 *
 * @class AwsRekognitionInterface
 */
interface AwsRekognitionInterface
{
    /**
     * Create a collection in AWS Rekognition (external collection).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return CreateCollectionResultData
     */
    public function createCollection(array $validatedRequest): CreateCollectionResultData;

    /**
     * List collections on the aws side (external collections).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return ListCollectionsResultData
     */
    public function listExternalCollections(array $validatedRequest): ListCollectionsResultData;

    /**
     * Delete a collection in AWS Rekognition (external collection).
     *
     * @param string $externalCollectionId
     *
     * @return DeleteCollectionResultData
     */
    public function deleteCollection(string $externalCollectionId): DeleteCollectionResultData;

    /**
     * Create a user in AWS Rekognition (external user).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return UserResultData
     */
    public function createUser(array $validatedRequest): UserResultData;

    /**
     * Delete a user in AWS Rekognition (external user).
     *
     * @param AwsUser $awsUser
     * @param string|null $clientRequestToken
     *
     * @return UserResultData
     */
    public function deleteUser(AwsUser $awsUser, ?string $clientRequestToken = null): UserResultData;

    /**
     * List users on the aws side (external users).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return ListUsersResultData
     */
    public function listExternalAwsUsers(array $validatedRequest): ListUsersResultData;

    /**
     * Index faces in AWS Rekognition.
     *
     * @param string $externalCollectionId
     * @param ImageData $imageData
     * @param string|null $externalImageId
     *
     * @return IndexFacesResultData
     */
    public function indexFaces(
        string $externalCollectionId,
        ImageData $imageData,
        ?string $externalImageId
    ): IndexFacesResultData;

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
    ): AssociateFacesResultData;

    /**
     * List external faces on the AWS side (external faces).
     *
     * @param array<string, mixed> $validatedRequest
     *
     * @return ListFacesResultData
     */
    public function listExternalFaces(array $validatedRequest): ListFacesResultData;

    /**
     * Delete faces from a collection in AWS Rekognition.
     *
     * @param array<string, mixed> $collectionAndFaceIds
     *
     * @return DeleteFacesResultData
     */
    public function deleteFaces(array $collectionAndFaceIds): DeleteFacesResultData;

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
    ): SearchUsersByImageResultData;
}
