<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalReportRequest;
use App\Http\Resources\MedicalReportResource;
use App\Models\MedicalReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicalReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = match ($request->query('trashed')) {
            'only' => MedicalReport::onlyTrashed(),
            'with' => MedicalReport::withTrashed(),
            default => MedicalReport::query(),
        };
        $query->with('patient');

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $reports = $query->latest()->paginate(min($request->integer('per_page', 20), 100));

        return response()->json([
            'data' => MedicalReportResource::collection($reports->items()),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * رفع التقرير عبر multipart/form-data (المريض + اسم التشخيص + ملف PDF أو صورة).
     * إنشاء تنبيه "تقرير جديد" يتم تلقائيًا عبر MedicalReportObserver.
     */
    public function store(StoreMedicalReportRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('reports', 'public');

        $report = MedicalReport::create([
            'patient_id' => $request->patient_id,
            'diagnosis_name' => $request->diagnosis_name,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => MedicalReport::detectFileType($file->getClientOriginalName()),
            'description' => $request->description,
        ]);

        return response()->json(new MedicalReportResource($report->load('patient')), 201);
    }

    public function show(MedicalReport $medicalReport): MedicalReportResource
    {
        return new MedicalReportResource($medicalReport->load('patient'));
    }

    /**
     * حذف ناعم فقط - سجل طبي، يُحتفَظ بسطره وبملفه الفعلي على القرص،
     * ولا يُحذفان نهائيًا إلا عبر forceDestroy() صراحة.
     */
    public function destroy(MedicalReport $medicalReport): JsonResponse
    {
        $medicalReport->delete();

        return response()->json(['message' => 'تم حذف التقرير بنجاح (يمكن استرجاعه لاحقًا)']);
    }

    public function restore(int $id): JsonResponse
    {
        $report = MedicalReport::onlyTrashed()->findOrFail($id);
        $report->restore();

        return response()->json([
            'message' => 'تم استرجاع التقرير بنجاح',
            'report' => new MedicalReportResource($report->load('patient')),
        ]);
    }

    /** حذف نهائي حقيقي (يُزيل السطر من قاعدة البيانات + الملف من القرص) - يتطلب أن يكون محذوفًا ناعمًا أولًا */
    public function forceDestroy(int $id): JsonResponse
    {
        $report = MedicalReport::onlyTrashed()->findOrFail($id);
        Storage::disk('public')->delete($report->file_path);
        $report->forceDelete();

        return response()->json(['message' => 'تم حذف التقرير نهائيًا']);
    }
}
