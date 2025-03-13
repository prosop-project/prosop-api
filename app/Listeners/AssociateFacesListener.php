<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AssociateFacesEvent;
use App\Jobs\AssociateFacesJob;

/**
 * @class AssociateFacesListener
 */
final readonly class AssociateFacesListener
{
    /**
     * Handle the event.
     *
     * @param AssociateFacesEvent $event
     *
     * @return void
     */
    public function handle(AssociateFacesEvent $event): void
    {
        AssociateFacesJob::dispatch($event->awsCollection, $event->awsUser, $event->externalFaceIds);
    }
}
