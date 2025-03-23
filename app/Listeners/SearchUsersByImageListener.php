<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Analysis\CreateAnalysisOperationAction;
use App\Enums\AnalysisOperationName;
use App\Events\SearchUsersByImageEvent;
use App\Jobs\SearchUsersByImageJob;

/**
 * @class SearchUsersByImageListener
 */
final readonly class SearchUsersByImageListener
{
    /**
     * @param CreateAnalysisOperationAction $createAnalysisOperationAction
     */
    public function __construct(private CreateAnalysisOperationAction $createAnalysisOperationAction) {}

    /**
     * Handle the event.
     *
     * @param SearchUsersByImageEvent $event
     *
     * @return void
     */
    public function handle(SearchUsersByImageEvent $event): void
    {
        // Prepare the data for the creation of the analysis operation and the search users by image job.
        $operation = AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value;
        $userId = $event->userId;
        $awsCollectionId = $event->awsCollectionId;
        $imagePath = $event->imagePath;
        $maxUsers = $event->maxUsers;
        $metadata = ! is_null($maxUsers) ? ['max_users' => $maxUsers] : null;

        // Create the analysis operation record in the database (analysis_operations table).
        $analysisOperation = $this->createAnalysisOperationAction->handle($userId, $awsCollectionId, $operation, $metadata);

        SearchUsersByImageJob::dispatch($analysisOperation, $imagePath);
    }
}
