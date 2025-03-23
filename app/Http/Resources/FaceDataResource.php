<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property float|null $confidence
 * @property string|null $external_face_id
 * @property string|null $image_id
 * @property string|null $external_user_id
 * @property string|null $external_image_id
 *
 * @class FaceDataResource
 */
final class FaceDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'confidence' => $this->confidence,
            'external_face_id' => $this->faceId,
            'image_id' => $this->imageId,
            'external_user_id' => $this->userId,
            'external_image_id' => $this->externalImageId,
        ];
    }
}
