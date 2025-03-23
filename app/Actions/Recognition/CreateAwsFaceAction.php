<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Models\AwsFace;
use App\Services\ActivityLog\CreateAwsFaceModelActivityService;
use Illuminate\Support\Facades\DB;

/**
 * @class CreateAwsFaceAction
 */
final readonly class CreateAwsFaceAction
{
    /**
     * @param CreateAwsFaceModelActivityService $activityService
     */
    public function __construct(private CreateAwsFaceModelActivityService $activityService) {}

    /**
     * @param array<int, array<string, mixed>> $faceParams
     *
     * @return bool
     */
    public function handle(array $faceParams): bool
    {
        return DB::transaction(function () use ($faceParams) {
            // Insert the faces
            $isInserted = AwsFace::query()->insert($faceParams);

            // Log the activity manually because insert bypasses the eloquent model activity so that activity is not logged by default.
            $this->activityService->log($faceParams);

            return $isInserted;
        });
    }
}
