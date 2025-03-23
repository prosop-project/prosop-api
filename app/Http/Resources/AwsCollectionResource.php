<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AwsCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AwsCollection
 */
final class AwsCollectionResource extends JsonResource
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
            'external_collection_id' => $this->external_collection_id,
            'external_collection_arn' => $this->external_collection_arn,
            'tags' => $this->tags,
            'face_model_version' => $this->face_model_version,
        ];
    }
}
