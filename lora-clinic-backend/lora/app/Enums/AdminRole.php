<?php

namespace App\Enums;

/**
 * دور المستخدم الإداري داخل لوحة التحكم.
 */
enum AdminRole: string
{
    case SuperAdmin = 'super_admin'; // مدير النظام (صلاحيات كاملة)
    case Staff = 'staff';            // موظف (صلاحيات محدودة)

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'مدير النظام',
            self::Staff => 'موظف',
        };
    }
}
