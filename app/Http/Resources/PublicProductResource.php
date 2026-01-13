<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PublicProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->base_price,
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'ingredients' => $this->ingredients_list,
            'has_variants' => $this->has_variants,
            'variants' => $this->when(
                $this->has_variants,
                fn() => PublicProductVariantResource::collection(
                    $this->variants->where('is_available', true)->sortBy('sort_order')
                )
            ),
            'tags' => $this->tags->map(fn($tag) => [
                'name' => $tag->name,
                'icon' => $tag->icon,
            ]),
        ];
    }
}
