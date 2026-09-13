<?php

namespace App\Http\Requests;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'operation_type' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'cost_amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:total_amount'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['required', 'date'],
            'status' => ['nullable', Rule::enum(InvoiceStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'المريض',
            'operation_type' => 'نوع العملية',
            'total_amount' => 'المبلغ الإجمالي',
            'cost_amount' => 'سعر التكلفة',
            'paid_amount' => 'المبلغ المدفوع',
            'due_date' => 'تاريخ الاستحقاق',
            'status' => 'حالة الفاتورة',
        ];
    }
}
