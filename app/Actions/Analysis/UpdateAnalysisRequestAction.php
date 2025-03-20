<?php

declare(strict_types=1);

namespace App\Actions\Analysis;

use App\Models\AnalysisRequest;

/**
 * @class UpdateAnalysisRequestAction
 */
final readonly class UpdateAnalysisRequestAction
{
    /**
     * Handle the action.
     *
     * @param AnalysisRequest $analysisRequest
     * @param string $status
     *
     * @return void
     */
    public function handle(AnalysisRequest $analysisRequest, string $status): void
    {
        // Update the analysis request record in the database (analysis_requests table).
        $analysisRequest->update([
            'status' => $status,
        ]);
    }
}
