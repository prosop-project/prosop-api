<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Recognition\CreateAwsFaceAction;
use App\Events\AssociateFacesEvent;
use App\Models\AwsCollection;
use App\Models\AwsUser;
use App\Models\User;
use App\Services\Recognition\AwsRekognitionInterface;
use App\Traits\PrepareImageDataTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @class IndexFacesJob
 */
final class IndexFacesJob implements ShouldQueue
{
    use PrepareImageDataTrait, Queueable;

    /**
     * Create a new job instance.
     *
     * @param int $awsCollectionId
     * @param array<int, string> $imagePaths
     * @param User $user
     */
    public function __construct(protected int $awsCollectionId, protected array $imagePaths, protected User $user) {}

    /**
     * Execute the job.
     *
     * @param AwsRekognitionInterface $awsRekognitionService
     * @param CreateAwsFaceAction $createAwsFaceAction
     *
     * @return void
     */
    public function handle(AwsRekognitionInterface $awsRekognitionService, CreateAwsFaceAction $createAwsFaceAction): void
    {
        // Retrieve the AWS collection
        $awsCollection = AwsCollection::query()->findOrFail($this->awsCollectionId);

        // Retrieve the AWS user
        $awsUser = AwsUser::query()
            ->where('aws_collection_id', $this->awsCollectionId)
            ->where('external_user_id', generate_external_id($this->user->id))
            ->firstOrFail();

        $faceParams = [];

        foreach ($this->imagePaths as $imagePath) {
            // Prepare the image data and generate an external image id
            $imageData = $this->prepareImageData($imagePath);
            $externalImageId = $this->generateExternalImageId($imagePath, $this->user);

            // Index faces in the image
            $indexFacesResultData = $awsRekognitionService->indexFaces(
                $awsCollection->external_collection_id,
                $imageData,
                $externalImageId
            );

            // Delete the image file after indexing the faces
            Storage::delete($imagePath);

            // We only index one face per image, so we get the first face record
            $faceRecord = Arr::get($indexFacesResultData->faceRecords?->items(), 0);

            // If no face is detected in the image, skip to the next image without adding it to the results
            if (! $faceRecord) {
                continue;
            }

            // Add the face parameters to the array
            $faceParams[] = [
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

        /*
         * If no faces are detected in any of the images, return.
         * Here we will also notify the user that no faces were detected in provided images.
         */
        if (empty($faceParams)) {
            return;
        }

        // Store the created faces to the aws_faces table (bulk insert), and delete if the limit is exceeded.
        $createAwsFaceAction->handle($faceParams);

        // Retrieve the external face ids
        $externalFaceIds = Arr::pluck($faceParams, 'external_face_id');

        // Fire the event to associate the faces which calls queue job AssociateFacesJob
        event(new AssociateFacesEvent($awsCollection, $awsUser, $externalFaceIds));
    }

    /**
     * Generate an external image id for the image.
     *
     * @param string $imagePath
     * @param User $user
     *
     * @return string
     */
    private function generateExternalImageId(string $imagePath, User $user): string
    {
        // Get the file extension from the path
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        // Get the file size in bytes
        $fileSize = Storage::fileSize($imagePath);
        // Convert the file size to kilobytes
        $imageSizeKb = (int) round($fileSize / 1024);

        // Generate the external image id by using the generate_external_id helper function
        return generate_external_id(
            userId: $user->id,
            includeRegion: true,
            extraComponents: [$extension, $imageSizeKb, Str::random(5)]
        );
    }
}
