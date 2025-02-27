<?php

declare(strict_types=1);

namespace App\Actions\Link;

use App\Http\Requests\Link\CreateLinkRequest;
use App\Models\Link;
use App\Models\User;

/**
 * @class CreateLinkAction
 */
final readonly class CreateLinkAction
{
    /**
     * Handle the action.
     *
     * @param CreateLinkRequest $request
     * @param User $user
     *
     * @return Link
     */
    public function handle(CreateLinkRequest $request, User $user): Link
    {
        // Create a new link.
        return Link::query()->create([
            'user_id' => $user->id,
            'type' => $request->type,
            'description' => $request->description,
            'value' => $request->value,
            'is_visible' => $request->is_visible,
        ]);
    }
}
