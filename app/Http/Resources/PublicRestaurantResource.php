<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PublicRestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'classification' => $this->classification,
            'description' => $this->description,
            'address' => $this->address,
            'phone' => $this->phone,
            'logo_url' => $this->logo_path ? Storage::url($this->logo_path) : null,
            'categories' => PublicCategoryResource::collection($this->categories),
        ];
    }
}
