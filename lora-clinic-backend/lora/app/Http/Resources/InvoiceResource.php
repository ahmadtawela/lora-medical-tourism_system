<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Invoice */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'patient' => [
                'id' => $this->patient->id,
                'name' => $this->patient->name,
            ],
            'operation_type' => $this->operation_type,
            'total_amount' => (float) $this->total_amount,
            'cost_amount' => (float) $this->cost_amount,
            'paid_amount' => (float) $this->paid_amount,
            'remaining_amount' => (float) $this->remaining_amount,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            // نسبة السداد الظاهرة في شريط التقدم بصفحة تفاصيل الفاتورة
            'payment_percentage' => $this->total_amount > 0
                ? (int) round(((float) $this->paid_amount / (float) $this->total_amount) * 100)
                : 0,
            'issue_date' => $this->issue_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
        ];
    }
}
