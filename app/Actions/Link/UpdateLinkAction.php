<?php

declare(strict_types=1);

namespace App\Actions\Link;

use App\Http\Requests\Link\UpdateLinkRequest;
use App\Models\Link;

/**
 * @class UpdateLinkAction
 */
final readonly class UpdateLinkAction
{
    /**
     * Handle the action.
     *
     * @param UpdateLinkRequest $request
     * @param Link $link
     *
     * @return void
     */
    public function handle(UpdateLinkRequest $request, Link $link): void
    {
        $validatedRequest = $request->validated();

        // If the link value is being changed, then reset the click count.
        if ($request->has('value') && $link->value !== $request->input('value')) {
            $validatedRequest = array_merge($validatedRequest, ['click_count' => 0]);
        }

        // Update the link.
        $link->update($validatedRequest);
    }
}
