<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property Collection<int, FaceDataResource> $faces
 * @property string|null $next_token
 *
 * @class ListExternalFacesResource
 */
final class ListExternalFacesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Manually convert DataCollection to a Laravel Collection
        $facesCollection = collect();

        // Convert each item in the DataCollection to a FaceDataResource
        foreach ($this->faces as $face) {
            $facesCollection->push(new FaceDataResource($face));
        }

        return [
            'faces' => $facesCollection,
            'next_token' => $this->nextToken,
        ];
    }
}
