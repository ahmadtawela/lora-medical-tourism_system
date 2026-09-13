<?php

namespace App\Enums;

/**
 * نوع التنبيه - يطابق تبويبات صفحة "التنبيهات"
 * (تقارير جديدة / دفعات متأخرة / عمليات قادمة)
 * ملاحظة: "غير مقروء" ليس نوعًا بل فلتر على عمود is_read.
 */
enum NotificationType: string
{
    case NewReport = 'new_report';               // تقارير جديدة
    case PaymentOverdue = 'payment_overdue';      // دفعات متأخرة
    case UpcomingOperation = 'upcoming_operation'; // عمليات قادمة

    public function label(): string
    {
        return match ($this) {
            self::NewReport => 'تقرير جديد',
            self::PaymentOverdue => 'دفع متأخر',
            self::UpcomingOperation => 'عملية قادمة',
        };
    }

    /** اسم أيقونة/لون الشارة المستخدم في الواجهة لكل نوع */
    public function severity(): string
    {
        return match ($this) {
            self::NewReport => 'info',
            self::PaymentOverdue => 'danger',
            self::UpcomingOperation => 'danger',
        };
    }
}
