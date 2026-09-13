<?php

namespace Database\Seeders;

use App\Models\MedicalReport;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class MedicalReportSeeder extends Seeder
{
    /**
     * ملاحظة: تقرير نورا القحطاني (رقم 6) لم يكن ظاهرًا في لقطة "التقارير الطبية"
     * الأصلية، لكنه أُضيف هنا لأن تنبيه "رفع تقرير طبي جديد - نورا القحطاني"
     * كان يشير إليه في صفحة التنبيهات - بدونه تكون بيانات العرض غير متسقة.
     */
    public function run(): void
    {
        $reports = [
            ['email' => 'khalid.balushi@email.com', 'diagnosis' => 'تلف الأسنان - نخر متقدم', 'file' => 'اشعة_اسنان_خالد.png', 'type' => 'image', 'desc' => 'تلف في 8 أسنان يحتاج إلى زراعة كاملة', 'days_ago' => 155],
            ['email' => 'fatima.rashid@email.com', 'diagnosis' => 'السمنة - الدرجة الثالثة', 'file' => 'صورة_فحص_فاطمة.jpg', 'type' => 'image', 'desc' => 'مؤشر كتلة الجسم 42، يُنصح بالتدخل الجراحي', 'days_ago' => 161],
            ['email' => 'ahmed.omari@email.com', 'diagnosis' => 'تساقط الشعر - الصلع الوراثي', 'file' => 'تقرير_فحص_الشعر_أحمد.pdf', 'type' => 'pdf', 'desc' => 'تساقط متقدم في المنطقة الأمامية والجانبية', 'days_ago' => 165],
            ['email' => 'sara.tamimi@email.com', 'diagnosis' => 'تليف الكبد - مرحلة نهائية', 'file' => 'ملف_كبد_سارة.pdf', 'type' => 'pdf', 'desc' => 'تليف كبدي متقدم، الحالة مستقرة للزراعة', 'days_ago' => 140],
            ['email' => 'm.dosari@email.com', 'diagnosis' => 'قصور القلب - المرحلة الثانية', 'file' => 'تقرير_قلب_محمد.pdf', 'type' => 'pdf', 'desc' => 'ضعف في عضلة القلب يستوجب جراحة عاجلة', 'days_ago' => 146],
            ['email' => 'nora.qahtani@email.com', 'diagnosis' => 'تجميل الأنف - فحص ما قبل العملية', 'file' => 'تقرير_تجميل_الأنف_نورا.pdf', 'type' => 'pdf', 'desc' => 'الفحص التمهيدي جيد، الترشيح مناسب لتصغير الأنف', 'days_ago' => 6],
        ];

        foreach ($reports as $data) {
            $patient = Patient::where('email', $data['email'])->first();

            if (! $patient) {
                continue;
            }

            $path = 'reports/'.$data['file'];

            // ملف نائب (placeholder) فقط لأغراض العرض التجريبي - يُستبدل بالملفات
            // الحقيقية المرفوعة من الأدمن أو المريض عبر الـ API
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put(
                    $path,
                    "ملف تجريبي (Placeholder) - {$data['diagnosis']}"
                );
            }

            $createdAt = Carbon::now()->subDays($data['days_ago']);

            $report = MedicalReport::updateOrCreate(
                ['patient_id' => $patient->id, 'file_path' => $path],
                [
                    'diagnosis_name' => $data['diagnosis'],
                    'file_name' => $data['file'],
                    'file_type' => $data['type'],
                    'description' => $data['desc'],
                ]
            );

            $report->timestamps = false;
            $report->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
            $report->timestamps = true;
        }
    }
}
