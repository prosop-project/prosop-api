<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ProcessFaceEvent;
use App\Jobs\IndexFacesJob;

/**
 * @class ProcessFaceListener
 */
final readonly class ProcessFaceListener
{
    /**
     * Handle the event.
     *
     * @param ProcessFaceEvent $event
     *
     * @return void
     */
    public function handle(ProcessFaceEvent $event): void
    {
        // Dispatch the queue job to index the faces
        IndexFacesJob::dispatch($event->awsCollectionId, $event->images, $event->user);
    }
}
