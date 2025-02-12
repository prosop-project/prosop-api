<?php

declare(strict_types=1);

namespace App\Http\Controllers\Link;

use App\Http\Controllers\Controller;
use App\Http\Requests\Link\CreateLinkRequest;
use App\Http\Requests\Link\DeleteLinkRequest;
use App\Http\Requests\Link\UpdateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use Illuminate\Http\JsonResponse;

/**
 * @class LinkController
 */
final readonly class LinkController extends Controller
{
    /**
     * Create a new link.
     *
     * @param CreateLinkRequest $request
     *
     * @return LinkResource
     */
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

    /**
     * Delete the link with given link id, form request checks for the authorization.
     *
     * @param DeleteLinkRequest $_
     * @param Link $link
     *
     * @return JsonResponse
     */
    public function delete(DeleteLinkRequest $_, Link $link): JsonResponse
    {
        // Delete the link.
        $link->delete();

        return response()->json(['message' => 'Link deleted successfully!']);
    }

    /**
     * Update the link with the given link id.
     *
     * @param UpdateLinkRequest $request
     * @param Link $link
     *
     * @return JsonResponse
     */
    public function update(UpdateLinkRequest $request, Link $link): JsonResponse
    {
        $validatedRequest = $request->validated();

        // If the link value is being changed, then reset the click count.
        if ($request->has('value') && $link->value !== $request->input('value')) {
            $validatedRequest = array_merge($validatedRequest, ['click_count' => 0]);
        }

        $link->update($validatedRequest);

        return response()->json(['message' => 'Link updated successfully!']);
    }
}
