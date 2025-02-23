<?php

declare(strict_types=1);

namespace App\Http\Controllers\ActivityLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityLog\DeleteActivityLogRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

/**
 * @class ActivityLogController
 */
final readonly class ActivityLogController extends Controller
{
    /**
     * Delete activity log records older than the given days.
     *
     * @param DeleteActivityLogRequest $request
     *
     * @return JsonResponse
     */
    public function __invoke(DeleteActivityLogRequest $request): JsonResponse
    {
        /*
         * Get the number of days to keep the activity log records, and also the log name for filtering the activity logs.
         */
        $days = $request->query('days', config('activitylog.delete_records_older_than_days'));
        $logName = $request->query('log_name');

        // Clean the activity log records command.
        Artisan::call('activitylog:clean ' . $logName . ' --force --days=' . $days);

        return response()->json(['message' => 'Activity log is cleaned!']);
    }
}
