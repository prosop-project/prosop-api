<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AnalysisOperation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AnalysisOperation
 */
final class UserAnalysisOperationsResource extends JsonResource
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
            'aws_collection_id' => $this->aws_collection_id,
            'operation' => $this->operation,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'aws_similarity_results' => AwsSimilarityResultResource::collection($this->whenLoaded('awsSimilarityResults')),
        ];
    }
}
