<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DeleteUserEvent;
use App\Jobs\DeleteUserJob;

/**
 * @class DeleteUserListener
 */
final readonly class DeleteUserListener
{
    /**
     * Handle the event.
     *
     * @param DeleteUserEvent $event
     *
     * @return void
     */
    public function handle(DeleteUserEvent $event): void
    {
        // Dispatch the queue job to delete the user
        DeleteUserJob::dispatch($event->user);
    }
}
