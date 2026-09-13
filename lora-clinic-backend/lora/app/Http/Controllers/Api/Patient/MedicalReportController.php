<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientMedicalReportRequest;
use App\Http\Resources\MedicalReportResource;
use App\Models\MedicalReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * يطابق صفحة "تقاريري" في بوابة المريض: عرض التقارير الخاصة به فقط،
 * ورفع تقرير جديد عبر نموذج "رفع تقرير طبي" (بدون حقل patient_id - يُشتق من المستخدم المسجّل).
 */
class MedicalReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reports = $request->user()->medicalReports()->with('patient')->latest()->paginate(min($request->integer('per_page', 20), 100));

        return response()->json([
            'data' => MedicalReportResource::collection($reports->items()),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function store(StorePatientMedicalReportRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('reports', 'public');

        $report = $request->user()->medicalReports()->create([
            'diagnosis_name' => $request->diagnosis_name,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => MedicalReport::detectFileType($file->getClientOriginalName()),
            'description' => $request->description,
        ]);

        return response()->json(new MedicalReportResource($report->load('patient')), 201);
    }
}
