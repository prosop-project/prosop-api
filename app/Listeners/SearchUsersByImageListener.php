<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Analysis\CreateAnalysisRequestAction;
use App\Enums\AnalysisOperation;
use App\Events\SearchUsersByImageEvent;
use App\Jobs\SearchUsersByImageJob;

/**
 * @class SearchUsersByImageListener
 */
final readonly class SearchUsersByImageListener
{
    /**
     * @param CreateAnalysisRequestAction $createAnalysisRequestAction
     */
    public function __construct(private CreateAnalysisRequestAction $createAnalysisRequestAction) {}

    /**
     * Handle the event.
     *
     * @param SearchUsersByImageEvent $event
     *
     * @return void
     */
    public function handle(SearchUsersByImageEvent $event): void
    {
        // Prepare the data for the creation of the analysis request and the search users by image job.
        $operation = AnalysisOperation::SEARCH_USERS_BY_IMAGE->value;
        $userId = $event->userId;
        $awsCollectionId = $event->awsCollectionId;
        $imagePath = $event->imagePath;
        $maxUsers = $event->maxUsers;
        $metadata = ! is_null($maxUsers) ? ['max_users' => $maxUsers] : null;

        // Create the analysis request record in the database (analysis_requests table).
        $analysisRequest = $this->createAnalysisRequestAction->handle($userId, $awsCollectionId, $operation, $metadata);

        SearchUsersByImageJob::dispatch($analysisRequest, $imagePath);
    }
}
