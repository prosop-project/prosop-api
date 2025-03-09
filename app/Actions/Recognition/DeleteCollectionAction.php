<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Models\AwsCollection;

/**
 * @class DeleteCollectionAction
 */
final readonly class DeleteCollectionAction
{
    /**
     * Handle the action.
     *
     * @param AwsCollection $awsCollection
     *
     * @return void
     */
    public function handle(AwsCollection $awsCollection): void
    {
        $awsCollection->delete();
    }
}
