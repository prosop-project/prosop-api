<?php

declare(strict_types=1);

namespace App\Http\Controllers\ActivityLog;

use App\Actions\ActivityLog\DeleteActivityLogAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityLog\DeleteActivityLogRequest;
use App\Http\Resources\GenericResponseResource;

/**
 * @class ActivityLogController
 */
final readonly class ActivityLogController extends Controller
{
    /**
     * Delete activity log records older than the given days.
     *
     * @param DeleteActivityLogRequest $request
     * @param DeleteActivityLogAction $deleteActivityLogAction
     *
     * @return GenericResponseResource
     */
    public function __invoke(
        DeleteActivityLogRequest $request,
        DeleteActivityLogAction $deleteActivityLogAction
    ): GenericResponseResource {
        $deleteActivityLogAction->handle($request);

        return new GenericResponseResource('Activity log is cleaned!');
    }
}
