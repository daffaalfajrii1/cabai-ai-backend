<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiseaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ai_class_name' => $this->ai_class_name,
            'slug' => $this->slug,
            'name' => $this->name,
            'scientific_name' => $this->scientific_name,
            'description' => $this->description,
            'symptoms' => $this->symptoms,
            'cause' => $this->cause,
            'treatment' => $this->treatment,
            'prevention' => $this->prevention,
            'is_healthy' => $this->is_healthy,
            'is_active' => $this->is_active,
        ];
    }
}
