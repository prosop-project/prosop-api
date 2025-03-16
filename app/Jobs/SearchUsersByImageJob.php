<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Recognition\UpdateAwsUserAction;
use App\Models\AwsCollection;
use App\Models\AwsUser;
use App\Services\Recognition\AwsRekognitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;

/**
 * @class SearchUsersByImageJob
 */
final class SearchUsersByImageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param int $awsCollectionId
     * @param UploadedFile $image
     * @param int|null $maxUsers
     */
    public function __construct(public int $awsCollectionId, public UploadedFile $image, public ?int $maxUsers = null) {}

    /**
     * Execute the job.
     *
     * @param AwsRekognitionService $awsRekognitionService
     * @param UpdateAwsUserAction $updateAwsUserAction
     *
     * @return void
     */
    public function handle(AwsRekognitionService $awsRekognitionService, UpdateAwsUserAction $updateAwsUserAction): void
    {
        // Retrieve the AWS collection and external collection id.
        $awsCollection = AwsCollection::query()->findOrFail($this->awsCollectionId);
        $externalCollectionId = $awsCollection->external_collection_id;

        // Search users by image in the AWS Rekognition collection.
        $searchUsersByImageResultData = $awsRekognitionService->searchUsersByImage(
            $externalCollectionId,
            $this->image,
            $this->maxUsers
        );

        // Update the AWS user with the external user status
        foreach ($searchUsersByImageResultData->userMatches as $match) {
            // Retrieve the external user id and external user status.
            $externalUserId = $match->user->userId;
            $externalUserStatus = $match->user->userStatus;

            // Retrieve the AWS user by the external user id.
            $awsUser = AwsUser::query()
                ->where('aws_collection_id', $this->awsCollectionId)
                ->where('external_user_id', $externalUserId)
                ->first();

            // Update the AWS user with the external user status if the aws user exists.
            if ($awsUser) {
                $updateAwsUserAction->handle($awsUser, $externalUserStatus);
            }
        }
    }
}
