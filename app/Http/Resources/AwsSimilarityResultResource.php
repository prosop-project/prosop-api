<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AwsSimilarityResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AwsSimilarityResult
 */
final class AwsSimilarityResultResource extends JsonResource
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
            'analysis_operation_id' => $this->analysis_operation_id,
            'aws_user' => new AwsUserResource($this->whenLoaded('awsUser')),
            'aws_face' => new AwsFaceResource($this->whenLoaded('awsFace')),
            'similarity' => $this->similarity,
            'metadata' => $this->metadata,
        ];
    }
}
