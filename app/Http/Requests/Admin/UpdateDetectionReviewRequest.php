<?php

namespace App\Http\Requests\Admin;

use App\Models\Detection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDetectionReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'review_status' => ['required', Rule::in(Detection::reviewStatuses())],
            'corrected_disease_id' => [
                Rule::requiredIf($this->input('review_status') === Detection::REVIEW_CORRECTED),
                'nullable',
                'integer',
                'exists:diseases,id',
            ],
            'review_notes' => [
                Rule::requiredIf($this->input('review_status') === Detection::REVIEW_REJECTED),
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
