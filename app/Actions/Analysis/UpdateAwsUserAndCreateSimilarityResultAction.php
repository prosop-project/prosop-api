<?php

declare(strict_types=1);

namespace App\Actions\Analysis;

use App\Actions\Recognition\UpdateAwsUserAction;
use App\Models\AwsUser;
use Illuminate\Support\Facades\DB;

/**
 * This action basically wraps UpdateAwsUserAction and CreateSimilarityResultAction to be able to handled them inside transaction.
 *
 * @class UpdateAwsUserAndCreateSimilarityResultAction
 */
final readonly class UpdateAwsUserAndCreateSimilarityResultAction
{
    /**
     * @param UpdateAwsUserAction $updateAwsUserAction
     * @param CreateAwsSimilarityResultAction $createAwsSimilarityResultAction
     */
    public function __construct(
        private UpdateAwsUserAction $updateAwsUserAction,
        private CreateAwsSimilarityResultAction $createAwsSimilarityResultAction,
    ) {}

    /**
     * Handle the action.
     *
     * @param AwsUser $awsUser
     * @param int $analysisOperationId
     * @param float $similarity
     * @param string|null $externalUserStatus
     *
     * @return void
     */
    public function handle(
        AwsUser $awsUser,
        int $analysisOperationId,
        float $similarity,
        ?string $externalUserStatus,
    ): void {
        DB::transaction(function () use ($awsUser, $analysisOperationId, $similarity, $externalUserStatus) {
            // Update the AWS user record in the database (aws_users table).
            $this->updateAwsUserAction->handle($awsUser, $externalUserStatus);

            // Create a new aws similarity result record in the database (aws_similarity_results table).
            $this->createAwsSimilarityResultAction->handle($analysisOperationId, $similarity, $awsUser->id);
        });
    }
}
