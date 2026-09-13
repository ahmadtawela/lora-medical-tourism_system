<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'invoice_number',
        'operation_type',
        'total_amount',
        'cost_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'issue_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'cost_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'issue_date' => 'date',
            'due_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // المتبقي = الإجمالي - المدفوع، يُحسب دائمًا من طرف الخادم
        // (الحقل غير موجود في نموذج الإنشاء/التعديل، فلا يجب الوثوق بأي قيمة قادمة من العميل له)
        static::saving(function (Invoice $invoice) {
            $invoice->remaining_amount = max(0, $invoice->total_amount - $invoice->paid_amount);
        });

        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
            if (empty($invoice->issue_date)) {
                $invoice->issue_date = now()->toDateString();
            }
        });
    }

    /** يولّد رقمًا تسلسليًا بصيغة INV-YYYY-001 */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() +1;

        return sprintf('INV-%d-%03d', $year, $count);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** هل تجاوزت الفاتورة تاريخ الاستحقاق وما زال عليها مبلغ متبقٍ؟ */
    public function isOverdue(): bool
    {
        return $this->status !== InvoiceStatus::Paid
            && (float) $this->remaining_amount > 0
            && $this->due_date?->isPast();
    }
}
