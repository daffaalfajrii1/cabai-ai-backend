<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DetectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $prediction = $this->needs_retake
            ? (object) []
            : [
                'class_id' => $this->ai_class_id,
                'class_name' => $this->ai_class_name,
                'confidence' => $this->confidence === null ? null : (float) $this->confidence,
                'confidence_percent' => $this->confidence_percent === null ? null : (float) $this->confidence_percent,
            ];

        return [
            'id' => $this->id,
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'needs_retake' => $this->needs_retake,
            'message' => $this->message,
            'status' => $this->status,
            'prediction' => $prediction,
            'top_predictions' => $this->needs_retake ? [] : ($this->top_predictions ?? []),
            'disease' => $this->needs_retake ? null : $this->whenLoaded('disease', fn () => $this->disease ? new DiseaseResource($this->disease) : null),
            'valid_input' => $this->valid_input,
            'classification_source' => $this->classification_source,
            'review' => [
                'status' => $this->reviewStatus(),
                'label' => $this->reviewStatusLabel(),
                'corrected_disease' => $this->whenLoaded('correctedDisease', fn () => $this->correctedDisease ? new DiseaseResource($this->correctedDisease) : null),
                'reviewed_by' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? new UserResource($this->reviewer) : null),
                'reviewed_at' => $this->reviewed_at?->toISOString(),
                'notes' => $this->review_notes,
            ],
            'user' => $this->whenLoaded('user', fn () => $this->user ? new UserResource($this->user) : null),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
