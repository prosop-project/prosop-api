<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @class SearchUsersByImageEvent
 */
final class SearchUsersByImageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param int $userId
     * @param int $awsCollectionId
     * @param string $imagePath
     * @param int|null $maxUsers
     */
    public function __construct(
        public int $userId,
        public int $awsCollectionId,
        public string $imagePath,
        public ?int $maxUsers = null
    ) {}
}
