<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;

/**
 * @class CreateAwsFaceModelActivityService
 */
final readonly class CreateAwsFaceModelActivityService
{
    /**
     * Log the activity.
     *
     * @param array<int, array<string, mixed>> $faceParams
     *
     * @return void
     */
    public function log(array $faceParams): void
    {
        activity(ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value)
            ->by(auth()->user())
            ->withProperties([
                'attributes' => $faceParams,
            ])
            ->event(ActivityEvent::CREATED->value)
            ->log('AwsFace records are created!');
    }
}
