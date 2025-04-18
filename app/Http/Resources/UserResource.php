<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_uuid' => $this->public_uuid,
            'name' => $this->name,
            'username' => $this->username,
            'description' => $this->description,
            'views' => $this->views,
            'avatar' => $this->avatar,
            'email' => $this->email,
            // Here we don't want to show the click_count and the is_visible fields of the links in the user resource.
            'links' => $this->whenLoaded('links', function () {
                return $this->links->map(function ($link) {
                    return new LinkResource(resource: $link, showClickCount: false, showIsVisible: false);
                });
            }),
        ];
    }
}
