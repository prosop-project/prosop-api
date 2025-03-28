<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Events\DeleteUserEvent;
use App\Models\User;

/**
 * @class UserService
 */
final readonly class UserService
{
    /**
     * Delete a user by dispatching an event.
     * (It will be handed by queue job including the relationship deletion - manual cascade delete)
     *
     * @param User $user
     *
     * @return void
     */
    public function deleteUser(User $user): void
    {
        // Dispatch the event to delete the user
        event(new DeleteUserEvent($user));
    }
}
