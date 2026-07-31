<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'diagnosis' => ['required', 'string'],
            'consultation_notes' => ['required', 'string'],
            'prescription' => ['nullable', 'string'],
            'treatment_plan' => ['nullable', 'string'],
            'follow_up_instructions' => ['nullable', 'string'],
        ];
    }
}
