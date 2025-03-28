<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Models\AwsFace;
use App\Services\ActivityLog\CreateAwsFaceModelActivityService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * @class CreateAwsFaceAction
 */
final readonly class CreateAwsFaceAction
{
    /**
     * @param CreateAwsFaceModelActivityService $activityService
     * @param DeleteFacesAction $deleteFacesAction
     */
    public function __construct(
        private CreateAwsFaceModelActivityService $activityService,
        private DeleteFacesAction $deleteFacesAction
    ) {}

    /**
     * @param array<int, array<string, mixed>> $faceParams
     *
     * @return bool
     */
    public function handle(array $faceParams): bool
    {
        // Delete the existing faces if the limit exceeds
        $this->deleteExistingFacesIfLimitExceeds($faceParams);

        return DB::transaction(function () use ($faceParams) {
            // Insert the faces
            $isInserted = AwsFace::query()->insert($faceParams);

            // Log the activity manually because insert bypasses the eloquent model activity so that activity is not logged by default.
            $this->activityService->log($faceParams);

            return $isInserted;
        });
    }

    /**
     * Delete the existing faces if the limit exceeds.
     *
     * @param array<int, array<string, mixed>> $faceParams
     *
     * @return void
     */
    private function deleteExistingFacesIfLimitExceeds(array $faceParams): void
    {
        // Get the aws user id from the first face params because it is the same for all faces.
        $awsUserId = Arr::get($faceParams, '0.aws_user_id');
        // Get the aws collection id from the first face params because it is the same for all faces.
        $awsCollectionId = Arr::get($faceParams, '0.aws_collection_id');

        // Get the previous faces
        $previousFaces = AwsFace::query()
            ->where('aws_user_id', $awsUserId)
            ->where('aws_collection_id', $awsCollectionId)
            ->get();

        // Get the total faces count (previous faces count + new faces count)
        $totalFacesCount = $previousFaces->count() + count($faceParams);

        // Check if the total faces count exceeds the limit, and delete the faces if it exceeds
        if ($totalFacesCount > (int) config('aws-rekognition.max_faces_per_user')) {
            // Group the aws faces by aws collection id.
            $groupedAwsFaces = $previousFaces
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

            // Delete the faces
            $this->deleteFacesAction->handle($groupedAwsFaces);
        }
    }
}
