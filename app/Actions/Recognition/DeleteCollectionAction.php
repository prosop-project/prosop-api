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
     * @param AwsCollection $collection
     *
     * @return void
     */
    public function handle(AwsCollection $collection): void
    {
        $collection->delete();
    }
}
