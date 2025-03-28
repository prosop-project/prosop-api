<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Actions\Analysis\DeleteAwsSimilarityResultAction;
use App\Models\AwsUser;
use App\Services\Recognition\AwsRekognitionService;
use Illuminate\Support\Facades\DB;

/**
 * @class DeleteAwsUserAction
 */
final readonly class DeleteAwsUserAction
{
    /**
     * @param DeleteAwsSimilarityResultAction $deleteAwsSimilarityResultAction
     * @param DeleteFacesAction $deleteFacesAction
     * @param AwsRekognitionService $awsRekognitionService
     */
    public function __construct(
        private DeleteAwsSimilarityResultAction $deleteAwsSimilarityResultAction,
        private DeleteFacesAction $deleteFacesAction,
        private AwsRekognitionService $awsRekognitionService
    ) {}


    /**
     * Handle the action.
     *
     * @param AwsUser $awsUser
     * @param string|null $clientRequestToken
     *
     * @return void
     */
    public function handle(AwsUser $awsUser, ?string $clientRequestToken = null): void
    {
        /*
         * Load the aws user with the related models.
         */
        $awsUser->load([
            'awsSimilarityResults',
            'awsFaces'
        ]);

        // Group the aws faces by aws collection id.
        $groupedAwsFaces = $awsUser->awsFaces
            ->groupBy('aws_collection_id')
            ->map(function ($faces, $collectionId) {
                return [
                    'aws_face_ids' => $faces->pluck('id')->all(),
                    'aws_collection_id' => $collectionId,
                    'external_face_ids' => $faces->pluck('external_face_id')->all(),
                ];
            })
            ->values()
            ->all();

        DB::transaction(function () use ($awsUser, $groupedAwsFaces, $clientRequestToken) {
            /*
             * Delete aws similarity result(s) from the aws_similarity_result model/table of the aws user.
             */
            $awsUser->awsSimilarityResults->each(function ($awsSimilarityResult) {
                $this->deleteAwsSimilarityResultAction->handle($awsSimilarityResult);
            });

            /*
             * Delete the faces from the database and AWS Rekognition side.
             */
            $this->deleteFacesAction->handle($groupedAwsFaces);

            /*
             * Delete a user on AWS Rekognition side by sending a request to AWS Rekognition API.
             */
            $this->awsRekognitionService->deleteUser($awsUser, $clientRequestToken);

            // Delete the aws user
            $awsUser->delete();
        });
    }
}
