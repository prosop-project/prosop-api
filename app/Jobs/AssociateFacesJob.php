<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Recognition\UpdateAwsUserAction;
use App\Models\AwsCollection;
use App\Models\AwsUser;
use App\Services\Recognition\AwsRekognitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * @class AssociateFacesJob
 */
final class AssociateFacesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param AwsCollection $awsCollection
     * @param AwsUser $awsUser
     * @param array<int, string> $externalFaceIds
     */
    public function __construct(
        public AwsCollection $awsCollection,
        public AwsUser $awsUser,
        public array $externalFaceIds
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AwsRekognitionService $awsRekognitionService, UpdateAwsUserAction $updateAwsUserAction): void
    {
        // Retrieve the AWS external collection id
        $externalCollectionId = $this->awsCollection->external_collection_id;

        // Retrieve the AWS external user id
        $externalUserId = $this->awsUser->external_user_id;

        // Associate faces by calling the AWS Rekognition service associateFaces method
        $associateFacesResultData = $awsRekognitionService->associateFaces($externalCollectionId, $this->externalFaceIds, $externalUserId);

        // Retrieve the external_user_status from the associateFacesResultData
        $externalUserStatus = $associateFacesResultData->userStatus;

        // Update the AWS user with the external user status
        $updateAwsUserAction->handle($this->awsUser, $externalUserStatus);
    }
}
