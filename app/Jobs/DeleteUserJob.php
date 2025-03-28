<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\User\DeleteUserAction;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * @class DeleteUserJob
 */
final class DeleteUserJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(protected User $user) {}

    /**
     * Execute the job.
     *
     * @param DeleteUserAction $deleteUserAction
     *
     * @return void
     */
    public function handle(DeleteUserAction $deleteUserAction): void
    {
        $deleteUserAction->handle($this->user);
    }
}
