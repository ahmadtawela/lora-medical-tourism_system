<?php

namespace App\Http\Requests;

use App\Enums\InterestLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // الصلاحية تُتحقق عبر middleware is.admin على مستوى الراوت
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:patients,email'],
            'phone_whatsapp' => ['required', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'country' => ['required', 'string', 'size:2'],
            'operation_type' => ['required', 'string', 'max:255'],
            'interest_level' => ['required', Rule::enum(InterestLevel::class)],
            'operation_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم الكامل',
            'email' => 'البريد الإلكتروني',
            'phone_whatsapp' => 'رقم الواتساب',
            'birth_date' => 'تاريخ الميلاد',
            'country' => 'الدولة',
            'operation_type' => 'نوع العملية',
            'interest_level' => 'مستوى الاهتمام',
        ];
    }
}
