<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property Collection<int, MatchedUserDataResource> $users
 * @property string|null $next_token
 *
 * @class ListExternalUsersResource
 */
final class ListExternalUsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Manually convert DataCollection to a Laravel Collection
        $usersCollection = collect();

        // Convert each item in the DataCollection to a MatchedUserDataResource
        foreach ($this->users as $user) {
            $usersCollection->push(new MatchedUserDataResource($user));
        }

        return [
            'users' => $usersCollection,
            'next_token' => $this->nextToken,
        ];
    }
}
