<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\UploadedFile;
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
     * @param int $awsCollectionId
     * @param UploadedFile $image
     * @param int|null $maxUsers
     */
    public function __construct(public int $awsCollectionId, public UploadedFile $image, public ?int $maxUsers = null) {}
}
