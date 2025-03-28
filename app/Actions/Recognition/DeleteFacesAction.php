<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Models\AwsFace;
use App\Services\Recognition\AwsRekognitionInterface;
use Illuminate\Support\Arr;

/**
 * @class DeleteFacesAction
 */
final readonly class DeleteFacesAction
{
    /**
     * @param AwsRekognitionInterface $awsRekognitionService
     */
    public function __construct(private AwsRekognitionInterface $awsRekognitionService) {}

    /**
     * Handle the action.
     *
     * @param array<int, array<string, mixed>> $groupedAwsFaces
     *
     * @return void
     */
    public function handle(array $groupedAwsFaces): void
    {
        // Loop through the grouped aws faces based on the aws collection id.
        foreach ($groupedAwsFaces as $collectionAndFaceIds) {
            // Delete a user on AWS Rekognition side by sending a request to AWS Rekognition API.
            $this->awsRekognitionService->deleteFaces($collectionAndFaceIds);

            // Retrieve the face ids from the collectionAndFaceIds array.
            $awsFaceIds = Arr::get($collectionAndFaceIds, 'aws_face_ids', []);

            // Delete the faces from the database (It triggers activity logs because destroy loads models before deleting them).
            AwsFace::destroy($awsFaceIds);
        }
    }
}
