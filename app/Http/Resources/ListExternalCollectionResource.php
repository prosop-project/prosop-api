<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array<int, string> $collection_ids
 * @property array<int, string> $face_model_versions
 * @property string|null $next_token
 */
final class ListExternalCollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'collection_ids' => $this->collectionIds,
            'face_model_versions' => $this->faceModelVersions,
            'next_token' => $this->nextToken,
        ];
    }
}
