<?php

declare(strict_types=1);

namespace App\Actions\Analysis;

use App\Models\AnalysisOperation;

/**
 * @class UpdateAnalysisOperationAction
 */
final readonly class UpdateAnalysisOperationAction
{
    /**
     * Handle the action.
     *
     * @param AnalysisOperation $analysisOperation
     * @param string $status
     *
     * @return void
     */
    public function handle(AnalysisOperation $analysisOperation, string $status): void
    {
        // Update the analysis operation record in the database (analysis_operations table).
        $analysisOperation->update([
            'status' => $status,
        ]);
    }
}
