<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * يطابق صفحة "فواتيري" في بوابة المريض - قراءة فقط، ومقيّدة بفواتير
 * المريض المسجّل دخوله حاليًا فقط.
 */
class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invoices = $request->user()->invoices()->with('patient')->latest()->paginate(min($request->integer('per_page', 20), 100));

        // نفس شكل {data, meta} المستخدم في نقاط نهاية الأدمن تمامًا (تجانس البيانات
        // عبر الـ API بالكامل، بدل شكل ترقيم الصفحات الافتراضي من Resource::collection)
        return response()->json([
            'data' => InvoiceResource::collection($invoices->items()),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    public function show(Request $request, Invoice $invoice): InvoiceResource
    {
        abort_if(
            $invoice->patient_id !== $request->user()->id,
            403,
            'غير مصرح لك بعرض هذه الفاتورة'
        );

        return new InvoiceResource($invoice->load('patient'));
    }
}
