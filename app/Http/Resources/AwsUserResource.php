<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AwsUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AwsUser
 */
final class AwsUserResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'aws_collection_id' => $this->aws_collection_id,
            'external_user_id' => $this->external_user_id,
            'external_user_status' => $this->external_user_status,
        ];
    }
}
