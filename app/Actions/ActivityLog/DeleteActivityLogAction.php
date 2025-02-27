<?php

declare(strict_types=1);

namespace App\Actions\ActivityLog;

use App\Http\Requests\ActivityLog\DeleteActivityLogRequest;
use App\Services\ActivityLog\ActivitylogCleanCommandActivityService;
use Illuminate\Support\Facades\Artisan;

/**
 * @class DeleteActivityLogAction
 */
final readonly class DeleteActivityLogAction
{
    /**
     * @param ActivitylogCleanCommandActivityService $activityService
     */
    public function __construct(protected ActivitylogCleanCommandActivityService $activityService) {}

    /**
     * Handle the action.
     *
     * @param DeleteActivityLogRequest $request
     *
     * @return void
     */
    public function handle(DeleteActivityLogRequest $request): void
    {
        /*
         * Get the number of days to keep the activity log records, and also the log name for filtering the activity logs.
         */
        $days = $request->query('days', config('activitylog.delete_records_older_than_days'));
        $logName = $request->query('log_name');

        // Clean the activity log records command.
        Artisan::call('activitylog:clean ' . $logName . ' --force --days=' . $days);

        // Log the activity.
        $this->activityService->log($days, $logName);
    }
}
