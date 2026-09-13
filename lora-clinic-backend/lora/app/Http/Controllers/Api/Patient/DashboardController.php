<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PatientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * يطابق شاشة "بوابتي" في بوابة المريض: رسالة ترحيب، المبلغ المتبقي،
 * عدد الفواتير، عدد التقارير، وبطاقة "آخر فاتورة".
 */
class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patient = $request->user();
        $patient->loadCount(['invoices', 'medicalReports']);

        $lastInvoice = $patient->invoices()->latest()->first();

        return response()->json([
            'patient' => new PatientResource($patient),
            'stats' => [
                'remaining_amount' => (float) $patient->invoices()->sum('remaining_amount'),
                'invoices_count' => $patient->invoices_count,
                'reports_count' => $patient->medical_reports_count,
            ],
            'last_invoice' => $lastInvoice ? new InvoiceResource($lastInvoice->load('patient')) : null,
        ]);
    }
}
