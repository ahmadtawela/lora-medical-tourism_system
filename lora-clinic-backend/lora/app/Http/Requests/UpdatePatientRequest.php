<?php

namespace App\Http\Requests;

use App\Enums\InterestLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->route('patient')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('patients', 'email')->ignore($patientId)],
            'phone_whatsapp' => ['sometimes', 'required', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'country' => ['sometimes', 'required', 'string', 'size:2'],
            'operation_type' => ['sometimes', 'required', 'string', 'max:255'],
            'interest_level' => ['sometimes', 'required', Rule::enum(InterestLevel::class)],
            'operation_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ];
    }
}
