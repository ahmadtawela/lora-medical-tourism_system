<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportPatientsRequest;
use App\Http\Resources\PatientResource;
use App\Services\PatientImportService;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PatientImportController extends Controller
{
    /**
     * يستقبل ملف Excel/CSV، يعالج كل صف بشكل مستقل (لا يوقف صف فاشل بقية
     * الصفوف)، وينشئ المرضى فعليًا مع إرسال دعوة تفعيل الحساب لكل واحد منهم.
     */
    public function import(ImportPatientsRequest $request, PatientImportService $importer): JsonResponse
    {
        $result = $importer->import($request->file('file')->getRealPath());

        return response()->json([
            'imported' => $result['imported'],
            'failed' => $result['failed'],
            'errors' => $result['errors'], // [{row, message}, ...]
            'patients' => PatientResource::collection($result['patients']),
        ]);
    }

    /** ملف Excel نموذجي جاهز بالأعمدة الصحيحة + صف مثال، ليعرف الأدمن الصيغة المتوقعة بالضبط */
    public function downloadTemplate(): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'الاسم الكامل', 'البريد الإلكتروني', 'رقم الواتساب', 'الدولة', 'نوع العملية',
            'مستوى الاهتمام', 'تاريخ الميلاد', 'تاريخ العملية', 'ملاحظات',
            'المبلغ الإجمالي', 'سعر التكلفة', 'المبلغ المدفوع', 'تاريخ الاستحقاق',
        ];
        $example = [
            'أحمد محمد العمري', 'ahmed@example.com', '+966501234567', 'SA', 'زراعة الشعر',
            'مهتم جدًا', '1990-05-12', '2026-09-01', 'مثال توضيحي - احذف هذا الصف قبل الرفع',
            '3500', '1800', '0', '2026-10-01',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($example, null, 'A2');
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'patients_template_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, 'نموذج_استيراد_المرضى.xlsx')
            ->deleteFileAfterSend()
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'patients_import_template.xlsx');
    }
}
