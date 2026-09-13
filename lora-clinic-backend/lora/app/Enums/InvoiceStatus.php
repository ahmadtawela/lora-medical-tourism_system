<?php

namespace App\Enums;

/**
 * حالة الفاتورة - تطابق شارات الحالة في صفحة "الفواتير"
 * (مدفوعة / جزئي / متأخرة / معلقة)
 */
enum InvoiceStatus: string
{
    case Pending = 'pending';   // معلقة
    case Partial = 'partial';   // جزئي
    case Paid = 'paid';         // مدفوعة
    case Overdue = 'overdue';   // متأخرة

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'معلقة',
            self::Partial => 'جزئي',
            self::Paid => 'مدفوعة',
            self::Overdue => 'متأخرة',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
