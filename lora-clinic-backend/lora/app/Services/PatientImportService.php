<?php

namespace App\Services;

use App\Enums\InterestLevel;
use App\Models\Invoice;
use App\Models\Patient;
use App\Notifications\PatientAccountCreated;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * استيراد مرضى بالجملة من ملف Excel/CSV. كل صف يُعالَج ويُتحقَّق منه بشكل
 * مستقل تمامًا - فشل صف واحد لا يوقف بقية الصفوف، بل يُسجَّل كخطأ برقم
 * الصف الفعلي في الملف ليسهل على الأدمن تصحيحه ورفعه مجددًا.
 */
class PatientImportService
{
    /**
     * أسماء الأعمدة المقبولة لكل حقل (عربي/إنجليزي، بدون حساسية لحالة الأحرف
     * أو المسافات الزائدة) - كل هذه الأسماء تُطابَق مع نفس الحقل النهائي.
     */
    private const HEADER_ALIASES = [
        'name' => ['الاسم', 'الاسم الكامل', 'اسم المريض', 'name', 'full name', 'patient name'],
        'email' => ['البريد الإلكتروني', 'الايميل', 'البريد', 'email', 'e-mail'],
        'phone_whatsapp' => ['رقم الواتساب', 'الواتساب', 'رقم الجوال', 'الجوال', 'الهاتف', 'whatsapp', 'phone'],
        'country' => ['الدولة', 'البلد', 'country'],
        'operation_type' => ['نوع العملية', 'العملية', 'operation', 'operation type'],
        'interest_level' => ['مستوى الاهتمام', 'الاهتمام', 'interest', 'interest level'],
        'birth_date' => ['تاريخ الميلاد', 'birth date', 'birthdate'],
        'operation_date' => ['تاريخ العملية', 'operation date'],
        'note' => ['ملاحظات', 'ملاحظة', 'notes', 'note'],
        'total_amount' => ['المبلغ', 'المبلغ الإجمالي', 'الإجمالي', 'amount', 'total', 'total amount'],
        'cost_amount' => ['سعر التكلفة', 'التكلفة', 'cost', 'cost amount'],
        'paid_amount' => ['المبلغ المدفوع', 'المدفوع', 'paid', 'paid amount'],
        'due_date' => ['تاريخ الاستحقاق', 'due date'],
    ];

    private const COUNTRY_NAME_TO_CODE = [
        'السعودية' => 'SA', 'الامارات' => 'AE', 'الإمارات' => 'AE', 'الكويت' => 'KW',
        'قطر' => 'QA', 'البحرين' => 'BH', 'عمان' => 'OM', 'عُمان' => 'OM', 'العراق' => 'IQ',
        'مصر' => 'EG', 'الاردن' => 'JO', 'الأردن' => 'JO', 'ليبيا' => 'LY', 'تونس' => 'TN',
        'المغرب' => 'MA', 'تركيا' => 'TR', 'سوريا' => 'SY',
    ];

    private const INTEREST_LABEL_TO_VALUE = [
        'مهتم جدا' => 'very_interested', 'مهتم جداً' => 'very_interested', 'مهتم جدًا' => 'very_interested',
        'مهتم' => 'interested',
        'متابعة' => 'follow_up',
        'غير مهتم' => 'not_interested',
    ];

    /**
     * @return array{imported: int, failed: int, errors: array<array{row: int, message: string}>, patients: array}
     */
    public function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return ['imported' => 0, 'failed' => 0, 'errors' => [['row' => 0, 'message' => 'الملف فارغ']], 'patients' => []];
        }

        $headerMap = $this->buildHeaderMap(array_shift($rows));

        $imported = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            // رقم الصف الفعلي في ملف Excel (بعد استبعاد رأس الجدول، وبدءًا من 1)
            $rowNumber = $index + 2;

            if ($this->isRowEmpty($row)) {
                continue; // تجاهل الصفوف الفارغة بصمت (شائعة في نهاية الملفات)
            }

            $data = $this->extractRowData($row, $headerMap);

            $result = $this->processRow($data, $rowNumber);
            if ($result['success']) {
                $imported[] = $result['patient'];
            } else {
                $errors[] = ['row' => $rowNumber, 'message' => $result['message']];
            }
        }

        return [
            'imported' => count($imported),
            'failed' => count($errors),
            'errors' => $errors,
            'patients' => $imported,
        ];
    }

    /** يطابق كل عمود في رأس الجدول مع اسم الحقل النهائي، مهما كان ترتيب الأعمدة */
    private function buildHeaderMap(array $headerRow): array
    {
        $map = []; // [column_index => field_name]

        foreach ($headerRow as $columnIndex => $rawHeader) {
            $normalized = $this->normalizeText((string) $rawHeader);
            if ($normalized === '') {
                continue;
            }

            foreach (self::HEADER_ALIASES as $field => $aliases) {
                foreach ($aliases as $alias) {
                    if ($this->normalizeText($alias) === $normalized) {
                        $map[$columnIndex] = $field;
                        continue 2;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * تطبيع نص عربي/إنجليزي للمقارنة المتسامحة: يوحّد أشكال الألف (أ إ آ ← ا)
     * والتاء المربوطة/الهاء (ة ← ه)، يحذف التشكيل، يحذف كل المسافات (بما فيها
     * المسافات غير القابلة للكسر التي يضيفها Excel أحيانًا)، ويحوّل لحروف صغيرة.
     * هذا يمنع فشل المطابقة بسبب فروق شكلية غير مرئية للعين.
     */
    private function normalizeText(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[\x{064B}-\x{0652}\x{0640}]/u', '', $value); // إزالة التشكيل والتطويل
        $value = str_replace(['أ', 'إ', 'آ'], 'ا', $value);
        $value = str_replace('ة', 'ه', $value);
        $value = str_replace('ى', 'ي', $value);
        $value = preg_replace('/\s+/u', '', $value); // إزالة كل المسافات (عادية أو غير قابلة للكسر)

        return mb_strtolower($value);
    }

    private function extractRowData(array $row, array $headerMap): array
    {
        $data = [];
        foreach ($headerMap as $columnIndex => $field) {
            $value = $row[$columnIndex] ?? null;
            $data[$field] = is_string($value) ? trim($value) : $value;
        }

        return $data;
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function processRow(array $data, int $rowNumber): array
    {
        $country = $this->mapCountry($data['country'] ?? '');
        $interestLevel = $this->mapInterestLevel($data['interest_level'] ?? '');
        $birthDate = $this->parseDate($data['birth_date'] ?? null);
        $operationDate = $this->parseDate($data['operation_date'] ?? null);

        $validator = Validator::make([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone_whatsapp' => $data['phone_whatsapp'] ?? null,
            'country' => $country,
            'operation_type' => $data['operation_type'] ?? null,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:patients,email'],
            'phone_whatsapp' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'operation_type' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'message' => implode('، ', $validator->errors()->all())];
        }

        $patient = Patient::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_whatsapp' => $data['phone_whatsapp'],
            'country' => $country,
            'operation_type' => $data['operation_type'],
            'interest_level' => $interestLevel,
            'birth_date' => $birthDate,
            'operation_date' => $operationDate,
            'note' => $data['note'] ?? null,
        ]);

        // يُرسل دعوة تفعيل الحساب فعليًا (بريد + واتساب)، بنفس آلية الإضافة اليدوية تمامًا
        $plainToken = $patient->generatePasswordSetupToken();
        $patient->notify(new PatientAccountCreated($plainToken));

        // إن وُجد مبلغ في الصف، تُنشأ فاتورة مرتبطة بالمريض تلقائيًا
        if (! empty($data['total_amount']) && is_numeric($data['total_amount'])) {
            Invoice::create([
                'patient_id' => $patient->id,
                'operation_type' => $data['operation_type'],
                'total_amount' => (float) $data['total_amount'],
                'cost_amount' => is_numeric($data['cost_amount'] ?? null) ? (float) $data['cost_amount'] : (float) $data['total_amount'],
                'paid_amount' => is_numeric($data['paid_amount'] ?? null) ? (float) $data['paid_amount'] : 0,
                'due_date' => $this->parseDate($data['due_date'] ?? null) ?? now()->addDays(30)->toDateString(),
            ]);
        }

        return ['success' => true, 'patient' => $patient];
    }

    private function mapCountry(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // كود دولة بحرفين مباشرة (مثال: SA)
        if (mb_strlen($value) === 2) {
            return mb_strtoupper($value);
        }

        $normalized = $this->normalizeText($value);
        foreach (self::COUNTRY_NAME_TO_CODE as $name => $code) {
            if ($this->normalizeText($name) === $normalized) {
                return $code;
            }
        }

        return null;
    }

    private function mapInterestLevel(string $value): string
    {
        $value = trim((string) $value);

        return self::INTEREST_LABEL_TO_VALUE[$value] ?? InterestLevel::Interested->value;
    }

    /** يتعامل مع التواريخ سواء كانت نصًا (2025-03-15) أو رقمًا تسلسليًا من Excel، أو فارغة (null) */
    private function parseDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $timestamp = strtotime((string) $value);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}
