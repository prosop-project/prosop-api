<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\AwsCollection;
use App\Models\AwsUser;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @class AssociateFacesEvent
 */
final class AssociateFacesEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param AwsCollection $awsCollection
     * @param AwsUser $awsUser
     * @param array<int, string> $externalFaceIds
     */
    public function __construct(
        public AwsCollection $awsCollection,
        public AwsUser $awsUser,
        public array $externalFaceIds
    ) {}
}
