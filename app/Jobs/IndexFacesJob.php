<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Recognition\CreateAwsFaceAction;
use App\Events\AssociateFacesEvent;
use App\Models\AwsCollection;
use App\Models\AwsUser;
use App\Models\User;
use App\Services\Recognition\AwsRekognitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

/**
 * @class IndexFacesJob
 */
final class IndexFacesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param int $awsCollectionId
     * @param array<int, UploadedFile> $images
     * @param User $user
     */
    public function __construct(public int $awsCollectionId, public array $images, public User $user) {}

    /**
     * Execute the job.
     *
     * @param AwsRekognitionService $awsRekognitionService
     * @param CreateAwsFaceAction $createAwsFaceAction
     *
     * @return void
     */
    public function handle(AwsRekognitionService $awsRekognitionService, CreateAwsFaceAction $createAwsFaceAction): void
    {
        // Retrieve the AWS collection
        $awsCollection = AwsCollection::query()->findOrFail($this->awsCollectionId);

        // Retrieve the AWS user
        $awsUser = AwsUser::query()
            ->where('aws_collection_id', $this->awsCollectionId)
            ->where('external_user_id', generate_external_id($this->user->id))
            ->firstOrFail();

        $faceParams = [];

        foreach ($this->images as $image) {
            // Index faces in the image
            $indexFacesResultData = $awsRekognitionService->indexFaces($awsCollection->external_collection_id, $image, $this->user);

            // We only index one face per image, so we get the first face record
            $faceRecord = Arr::get($indexFacesResultData->faceRecords?->items(), 0);

            // If no face is detected in the image, skip to the next image without adding it to the results
            if (! $faceRecord) {
                continue;
            }

            // Add the face parameters to the array
            $faceParams[] = [
                'user_id' => $this->user->id,
                'aws_user_id' => $awsUser->id,
                'aws_collection_id' => $this->awsCollectionId,
                'external_face_id' => $faceRecord?->face?->faceId,
                'confidence' => $faceRecord?->face?->confidence,
                'external_image_id' => $faceRecord?->face?->externalImageId,
                'image_id' => $faceRecord?->face?->imageId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // If no faces are detected in any of the images, return. Here we will also notify the user that no faces were detected in provided images.
        if (empty($faceParams)) {
            return;
        }

        // Store the created faces to the aws_faces table (bulk insert)
        $createAwsFaceAction->handle($faceParams);

        // Retrieve the external face ids
        $externalFaceIds = Arr::pluck($faceParams, 'external_face_id');

        // Fire the event to associate the faces which calls queue job AssociateFacesJob
        event(new AssociateFacesEvent($awsCollection, $awsUser, $externalFaceIds));
    }
}
