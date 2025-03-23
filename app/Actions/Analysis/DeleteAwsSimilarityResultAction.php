<?php

declare(strict_types=1);

namespace App\Actions\Analysis;

use App\Models\AwsSimilarityResult;

/**
 * @class DeleteAwsSimilarityResultAction
 */
final readonly class DeleteAwsSimilarityResultAction
{
    /**
     * Handle the action.
     *
     * @param int|AwsSimilarityResult $awsSimilarityResult
     *
     * @return void
     */
    public function handle(int|AwsSimilarityResult $awsSimilarityResult): void
    {
        // Check if the $awsSimilarityResult is an integer. If it is not, it is an instance of AwsSimilarityResult.
        if (! ($awsSimilarityResult instanceof AwsSimilarityResult)) {
            // Retrieve the AwsSimilarityResult from the database. In order to trigger activity log, we first need to retrieve the model.
            $awsSimilarityResult = AwsSimilarityResult::query()->findOrFail($awsSimilarityResult);
        }

        // Delete the AwsSimilarityResult.
        $awsSimilarityResult->delete();
    }
}
