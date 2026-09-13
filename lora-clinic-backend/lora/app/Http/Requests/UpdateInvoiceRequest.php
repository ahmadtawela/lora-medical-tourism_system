<?php

namespace App\Http\Requests;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'operation_type' => ['sometimes', 'required', 'string', 'max:255'],
            'total_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'paid_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'issue_date' => ['sometimes', 'nullable', 'date'],
            'due_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'nullable', Rule::enum(InvoiceStatus::class)],
        ];
    }
}
