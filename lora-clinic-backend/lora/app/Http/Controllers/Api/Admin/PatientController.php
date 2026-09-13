<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Notifications\PatientAccountCreated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * يطابق فلاتر "كل المستويات" (interest_level) و"الكل" (country)
     * وحقل البحث "بالاسم أو الواتساب" في أعلى صفحة إدارة المرضى.
     */
    /**
     * ?trashed=only يعرض المرضى المحذوفين (حذفًا ناعمًا) فقط، ?trashed=with يعرضهم مع البقية.
     * بدون هذا المعامل، تُستثنى السجلات المحذوفة تلقائيًا (سلوك SoftDeletes الافتراضي).
     */
    public function index(Request $request): JsonResponse
    {
        $query = match ($request->query('trashed')) {
            'only' => Patient::onlyTrashed(),
            'with' => Patient::withTrashed(),
            default => Patient::query(),
        };
        $query->withCount(['invoices', 'medicalReports']);

        if ($request->filled('interest_level') && $request->interest_level !== 'all') {
            $query->where('interest_level', $request->interest_level);
        }

        if ($request->filled('country') && $request->country !== 'all') {
            $query->where('country', $request->country);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_whatsapp', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $patients = $query->latest()->paginate(min($request->integer('per_page', 20), 100));

        return response()->json([
            'data' => PatientResource::collection($patients->items()),
            'meta' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'total' => $patients->total(),
            ],
        ]);
    }

    /**
     * ينشئ سجل المريض بدون كلمة مرور إطلاقًا (لا الأدمن ولا الباك إند يضعانها)،
     * ثم يرسل له رابط دعوة آمنًا (بريد + واتساب) ليعيّنها بنفسه - النمط المتعارف
     * عليه في أي نظام حقيقي (تمامًا كدعوات Slack أو Google Workspace).
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = Patient::create($request->safe()->except('password'));

        $plainToken = $patient->generatePasswordSetupToken();
        $patient->notify(new PatientAccountCreated($plainToken));

        return response()->json([
            'patient' => new PatientResource($patient),
            'message' => 'تم إنشاء الحساب، وأُرسلت دعوة لتعيين كلمة المرور إلى بريد المريض وواتسابه',
        ], 201);
    }

    /** لإعادة إرسال رابط الدعوة (مثلًا إن ضاع أو انتهت صلاحيته) دون إنشاء حساب جديد */
    public function resendInvite(Patient $patient): JsonResponse
    {
        $plainToken = $patient->generatePasswordSetupToken();
        $patient->notify(new PatientAccountCreated($plainToken));

        return response()->json(['message' => 'تم إعادة إرسال رابط تعيين كلمة المرور']);
    }

    public function show(Patient $patient): PatientResource
    {
        $patient->load(['invoices', 'medicalReports']);

        return new PatientResource($patient);
    }

    /** الأدمن يعدّل بيانات المريض، لكن لا يقدر يضبط كلمة مروره أبدًا - فقط resendInvite() لذلك */
    public function update(UpdatePatientRequest $request, Patient $patient): PatientResource
    {
        $patient->update($request->validated());

        return new PatientResource($patient);
    }

    /** حذف ناعم: يخفي المريض فقط، دون المساس بفواتيره أو تقاريره الطبية */
    public function destroy(Patient $patient): JsonResponse
    {
        $patient->delete();

        return response()->json(['message' => 'تم حذف المريض بنجاح (يمكن استرجاعه لاحقًا)']);
    }

    public function restore(int $id): JsonResponse
    {
        $patient = Patient::onlyTrashed()->findOrFail($id);
        $patient->restore();

        return response()->json([
            'message' => 'تم استرجاع المريض بنجاح',
            'patient' => new PatientResource($patient),
        ]);
    }
}
