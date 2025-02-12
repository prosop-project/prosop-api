<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class ProfileResource extends JsonResource
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
            'name' => $this->name,
            'username' => $this->username,
            'description' => $this->description,
            'views' => $this->views,
            'avatar' => $this->avatar,
            'email' => $this->email,
            // Here we want to show the click_count and the is_visible fields of the links in the user profile resource.
            'links' => $this->whenLoaded('links', function () {
                return $this->links->map(function ($link) {
                    return new LinkResource(resource: $link, showClickCount: true, showIsVisible: true);
                });
            }),
        ];
    }
}
