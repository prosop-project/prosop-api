<?php

declare(strict_types=1);

namespace App\Actions\Analysis;

use App\Models\AnalysisOperation;
use Illuminate\Support\Facades\DB;

/**
 * @class DeleteAnalysisOperationAction
 */
final readonly class DeleteAnalysisOperationAction
{
    /**
     * @param DeleteAwsSimilarityResultAction $deleteAwsSimilarityResultAction
     */
    public function __construct(private DeleteAwsSimilarityResultAction $deleteAwsSimilarityResultAction) {}

    /**
     * Handle the action.
     *
     * @param int|AnalysisOperation $analysisOperation
     *
     * @return void
     */
    public function handle(int|AnalysisOperation $analysisOperation): void
    {
        // Check if the $analysisOperation is an integer. If it is not, it is an instance of AnalysisOperation.
        if (! ($analysisOperation instanceof AnalysisOperation)) {
            // Retrieve the AnalysisOperation from the database.
            $analysisOperation = AnalysisOperation::query()
                ->with('awsSimilarityResults')
                ->findOrFail($analysisOperation);
        }

        /*
         * Note!
         * Instead of cascadeOnDelete() in the migration file in aws_similarity_results table, we can delete the related records in the database manually.
         * Reason for that we want to trigger activity log for aws_similarity_results deletions as well.
         * Using cascadeOnDelete() would not trigger the activity log because it gets deleted directly from the database without triggering the Eloquent events.
         */
        DB::transaction(function () use ($analysisOperation) {
            // Delete the related AwsSimilarityResult records manually in the database (aws_similarity_results table)
            foreach ($analysisOperation->awsSimilarityResults as $awsSimilarityResult) {
                $this->deleteAwsSimilarityResultAction->handle($awsSimilarityResult); // This triggers the activity log
            }

            // Delete the AnalysisOperation record in the database (analysis_operations table).
            $analysisOperation->delete();
        });
    }
}
