<?php

declare(strict_types=1);

namespace App\Actions\Recognition;

use App\Models\AwsFace;

/**
 * @class DeleteFacesAction
 */
final readonly class DeleteFacesAction
{
    /**
     * Handle the action.
     *
     * @param array<int, int> $awsFaceIds
     *
     * @return void
     */
    public function handle(array $awsFaceIds): void
    {
        // Delete the faces from the database (It triggers activity logs because destroy loads models before deleting them).
        AwsFace::destroy($awsFaceIds);
    }
}
