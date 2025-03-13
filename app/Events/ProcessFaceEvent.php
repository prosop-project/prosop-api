<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\SerializesModels;

/**
 * @class ProcessFaceEvent
 */
final class ProcessFaceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param int $awsCollectionId
     * @param array<int, UploadedFile> $images
     * @param User $user
     */
    public function __construct(public int $awsCollectionId, public array $images, public User $user) {}
}
