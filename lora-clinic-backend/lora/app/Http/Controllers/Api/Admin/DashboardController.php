<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\PatientResource;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;

/**
 * يطابق أربع بطاقات الإحصائيات + "تنبيهات عاجلة" + "أحدث المرضى" + "آخر الفواتير" في لوحة التحكم.
 */
class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $overdueCount = Invoice::where('status', InvoiceStatus::Overdue->value)->count();

        // "فواتير معلقة" في لوحة التحكم = كل فاتورة لم تُسدَّد بالكامل بعد (تحتاج متابعة)
        // وهي أوسع من حالة "معلقة/pending" وحدها، لذلك نستثني المدفوعة فقط
        $needsFollowUpCount = Invoice::where('status', '!=', InvoiceStatus::Paid->value)->count();

        $collectedRevenue = (float) Invoice::sum('paid_amount');
        $totalPatients = Patient::count();

        $urgentNotifications = Notification::with('patient')
            ->where('is_read', false)
            ->latest()
            ->take(4)
            ->get();

        $recentPatients = Patient::latest()->take(5)->get();

        $recentInvoices = Invoice::with('patient')->latest()->take(5)->get();

        return response()->json([
            'stats' => [
                'overdue_invoices_count' => $overdueCount,
                'pending_invoices_count' => $needsFollowUpCount,
                'collected_revenue' => $collectedRevenue,
                'total_patients' => $totalPatients,
            ],
            'urgent_notifications' => NotificationResource::collection($urgentNotifications),
            'recent_patients' => PatientResource::collection($recentPatients),
            'recent_invoices' => InvoiceResource::collection($recentInvoices),
        ]);
    }
}
