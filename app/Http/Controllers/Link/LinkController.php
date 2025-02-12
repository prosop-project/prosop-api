<?php

declare(strict_types=1);

namespace App\Http\Controllers\Link;

use App\Http\Controllers\Controller;
use App\Http\Requests\Link\CreateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;

final readonly class LinkController extends Controller
{
    public function create(CreateLinkRequest $request): LinkResource
    {
        $link = Link::query()->create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'description' => $request->description,
            'value' => $request->value,
            'is_visible' => $request->is_visible,
        ]);

        return new LinkResource($link);
    }

    public function delete(): void
    {
        // Delete a link
    }

    public function update(): void
    {
        // Update a link
    }
}
