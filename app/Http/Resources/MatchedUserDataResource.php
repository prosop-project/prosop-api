<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $external_user_id
 * @property string $external_user_status
 *
 * @class MatchedUserDataResource
 */
final class MatchedUserDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'external_user_id' => $this->userId,
            'external_user_status' => $this->userStatus,
        ];
    }
}
