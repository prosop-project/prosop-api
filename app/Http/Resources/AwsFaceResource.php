<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AwsFace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AwsFace
 */
final class AwsFaceResource extends JsonResource
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
            'aws_user_id' => $this->aws_user_id,
            'aws_collection_id' => $this->aws_collection_id,
            'external_face_id' => $this->external_face_id,
            'confidence' => $this->confidence,
            'external_image_id' => $this->external_image_id,
            'image_id' => $this->image_id,
        ];
    }
}
