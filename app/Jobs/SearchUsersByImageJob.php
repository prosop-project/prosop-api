<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Analysis\UpdateAnalysisRequestAction;
use App\Actions\Analysis\UpdateAwsUserAndCreateSimilarityResultAction;
use App\Enums\Status;
use App\Models\AnalysisRequest;
use App\Models\AwsCollection;
use App\Models\AwsUser;
use App\Services\Recognition\AwsRekognitionService;
use App\Traits\PrepareImageDataTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;

/**
 * @class SearchUsersByImageJob
 */
final class SearchUsersByImageJob implements ShouldQueue
{
    use PrepareImageDataTrait, Queueable;

    /**
     * Create a new job instance.
     *
     * @param AnalysisRequest $analysisRequest
     * @param string $imagePath
     */
    public function __construct(protected AnalysisRequest $analysisRequest, protected string $imagePath) {}

    /**
     * Execute the job.
     *
     * @param AwsRekognitionService $awsRekognitionService
     * @param UpdateAwsUserAndCreateSimilarityResultAction $updateAwsUserAndCreateSimilarityResultAction
     * @param UpdateAnalysisRequestAction $updateAnalysisRequestAction
     *
     * @return void
     */
    public function handle(
        AwsRekognitionService $awsRekognitionService,
        UpdateAwsUserAndCreateSimilarityResultAction $updateAwsUserAndCreateSimilarityResultAction,
        UpdateAnalysisRequestAction $updateAnalysisRequestAction,
    ): void {
        // Retrieve the AWS collection id and max users from the analysis request.
        $awsCollectionId = $this->analysisRequest->aws_collection_id;
        $maxUsers = Arr::get($this->analysisRequest->metadata, 'max_users');

        // Retrieve the AWS collection and external collection id.
        $awsCollection = AwsCollection::query()->findOrFail($awsCollectionId);
        $externalCollectionId = $awsCollection->external_collection_id;

        // Prepare the image data
        $imageData = $this->prepareImageData($this->imagePath);

        // Search users by image in the AWS Rekognition collection.
        $searchUsersByImageResultData = $awsRekognitionService->searchUsersByImage(
            $externalCollectionId,
            $imageData,
            $maxUsers
        );

        // Update the AWS user with the external user status
        foreach ($searchUsersByImageResultData->userMatches as $match) {
            // Retrieve the external user id and external user status.
            $externalUserId = $match->user->userId;
            $externalUserStatus = $match->user->userStatus;

            // Retrieve the AWS user by the external user id.
            $awsUser = AwsUser::query()
                ->where('aws_collection_id', $awsCollectionId)
                ->where('external_user_id', $externalUserId)
                ->first();

            if (! $awsUser) {
                // todo notification states that no user is found
                // Return early if the AWS user is not found.
                return;
            }

            // Update the AWS user record in the database (aws_users table) and create a new aws similarity result record in the database (aws_similarity_results table).
            $updateAwsUserAndCreateSimilarityResultAction->handle(
                $awsUser,
                $this->analysisRequest->id,
                $match->similarity,
                $externalUserStatus
            );
        }

        // Update the analysis request status to completed.
        $updateAnalysisRequestAction->handle($this->analysisRequest, Status::COMPLETED->value);

        // todo notify user that analysis is done with a redirect link maybe where analysis page will be opened with new analysis result
    }
}
