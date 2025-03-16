<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SearchUsersByImageEvent;
use App\Jobs\SearchUsersByImageJob;

/**
 * @class SearchUsersByImageListener
 */
final readonly class SearchUsersByImageListener
{
    /**
     * Handle the event.
     *
     * @param SearchUsersByImageEvent $event
     *
     * @return void
     */
    public function handle(SearchUsersByImageEvent $event): void
    {
        SearchUsersByImageJob::dispatch($event->awsCollectionId, $event->image, $event->maxUsers);
    }
}
