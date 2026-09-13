<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * يطابق تبويبات الفلترة (الكل/معلقة/متأخرة/جزني/مدفوعة) وبطاقات الإحصائيات
     * الأربع أعلى صفحة "الفواتير".
     */
    public function index(Request $request): JsonResponse
    {
        $query = match ($request->query('trashed')) {
            'only' => Invoice::onlyTrashed(),
            'with' => Invoice::withTrashed(),
            default => Invoice::query(),
        };
        $query->with('patient');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->latest()->paginate(min($request->integer('per_page', 20), 100));

        return response()->json([
            'data' => InvoiceResource::collection($invoices->items()),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'total' => $invoices->total(),
            ],
            'stats' => [
                'total_invoices' => Invoice::count(),
                'overdue_invoices' => Invoice::where('status', InvoiceStatus::Overdue->value)->count(),
                'pending_amount' => (float) Invoice::sum('remaining_amount'),
                'collected_amount' => (float) Invoice::sum('paid_amount'),
            ],
        ]);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = Invoice::create($request->validated());

        return response()->json(new InvoiceResource($invoice->load('patient')), 201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice->load('patient'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        $invoice->update($request->validated());

        return new InvoiceResource($invoice->fresh('patient'));
    }

    /** حذف ناعم - سجل مالي، يُحتفَظ به مخفيًا وليس محذوفًا نهائيًا */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();

        return response()->json(['message' => 'تم حذف الفاتورة بنجاح (يمكن استرجاعها لاحقًا)']);
    }

    public function restore(int $id): JsonResponse
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $invoice->restore();

        return response()->json([
            'message' => 'تم استرجاع الفاتورة بنجاح',
            'invoice' => new InvoiceResource($invoice->load('patient')),
        ]);
    }
}
