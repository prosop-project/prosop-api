<?php

declare(strict_types=1);

namespace App\Actions\Link;

use App\Models\Link;

/**
 * @class DeleteLinkAction
 */
final readonly class DeleteLinkAction
{
    /**
     * Handle the action.
     *
     * @param Link $link
     *
     * @return void
     */
    public function handle(Link $link): void
    {
        // Delete the link.
        $link->delete();
    }
}
