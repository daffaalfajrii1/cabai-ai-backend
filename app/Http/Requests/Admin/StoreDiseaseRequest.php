<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiseaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'ai_class_name' => ['required', 'string', 'max:255', 'unique:diseases,ai_class_name'],
            'name' => ['required', 'string', 'max:255'],
            'scientific_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'symptoms' => ['nullable', 'string'],
            'cause' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'prevention' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
