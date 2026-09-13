<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'diagnosis_name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB
            'description' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'المريض',
            'diagnosis_name' => 'اسم المرض / التشخيص',
            'file' => 'الملف',
        ];
    }
}
