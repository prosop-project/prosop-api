<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Link
 */
final class LinkResource extends JsonResource
{
    public function __construct(
        $resource,
        private readonly bool $showClickCount = true,
        private readonly bool $showIsVisible = true
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'value' => $this->value,
        ];

        // Only show the click count if the showClickCount property is true.
        if ($this->showClickCount) {
            $data['click_count'] = $this->click_count;
        }

        // Only show the is_visible field if the showIsVisible property is true.
        if ($this->showIsVisible) {
            $data['is_visible'] = $this->is_visible;
        }

        return $data;
    }
}
