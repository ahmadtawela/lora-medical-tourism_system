<?php

namespace App\Enums;

/**
 * مستوى اهتمام المريض - يطابق قائمة الفلترة في "إدارة المرضى"
 * (كل المستويات / مهتم جدًا / مهتم / متابعة / غير مهتم)
 */
enum InterestLevel: string
{
    case VeryInterested = 'very_interested'; // مهتم جدًا
    case Interested = 'interested';           // مهتم
    case FollowUp = 'follow_up';              // متابعة
    case NotInterested = 'not_interested';    // غير مهتم

    public function label(): string
    {
        return match ($this) {
            self::VeryInterested => 'مهتم جدًا',
            self::Interested => 'مهتم',
            self::FollowUp => 'متابعة',
            self::NotInterested => 'غير مهتم',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
