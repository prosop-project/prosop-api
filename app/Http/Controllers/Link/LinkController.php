<?php

declare(strict_types=1);

namespace App\Http\Controllers\Link;

use App\Actions\Link\CreateLinkAction;
use App\Actions\Link\DeleteLinkAction;
use App\Actions\Link\UpdateLinkAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Link\CreateLinkRequest;
use App\Http\Requests\Link\DeleteLinkRequest;
use App\Http\Requests\Link\UpdateLinkRequest;
use App\Http\Resources\GenericResponseResource;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use App\Models\User;

/**
 * @class LinkController
 */
final readonly class LinkController extends Controller
{
    /**
     * Create a new link.
     *
     * @param CreateLinkRequest $request
     * @param User $user
     * @param CreateLinkAction $createLinkAction
     *
     * @return LinkResource
     */
    public function create(
        CreateLinkRequest $request,
        User $user,
        CreateLinkAction $createLinkAction
    ): LinkResource {
        $link = $createLinkAction->handle($request, $user);

        return new LinkResource($link);
    }

    /**
     * Delete the link with given link id, form request checks for the authorization.
     *
     * @param DeleteLinkRequest $_
     * @param Link $link
     * @param DeleteLinkAction $deleteLinkAction
     *
     * @return GenericResponseResource
     */
    public function delete(
        DeleteLinkRequest $_,
        Link $link,
        DeleteLinkAction $deleteLinkAction
    ): GenericResponseResource {
        $deleteLinkAction->handle($link);

        return new GenericResponseResource('Link deleted successfully!');
    }

    /**
     * Update the link with the given link id.
     *
     * @param UpdateLinkRequest $request
     * @param Link $link
     * @param UpdateLinkAction $updateLinkAction
     *
     * @return GenericResponseResource
     */
    public function update(
        UpdateLinkRequest $request,
        Link $link,
        UpdateLinkAction $updateLinkAction
    ): GenericResponseResource {
        $updateLinkAction->handle($request, $link);

        return new GenericResponseResource('Link updated successfully!');
    }
}
