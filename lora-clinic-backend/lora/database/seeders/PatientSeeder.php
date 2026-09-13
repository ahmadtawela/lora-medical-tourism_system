<?php

namespace Database\Seeders;

use App\Enums\InterestLevel;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PatientSeeder extends Seeder
{
    /**
     * ملاحظة: نستخدم تواريخ نسبية (now()->subDays / addDays) بدل تواريخ 2025 ثابتة،
     * كي تبقى بيانات العرض التجريبي (العمليات القادمة، الفواتير المتأخرة...) واقعية
     * ومتناسقة مع أوامر الفحص المجدولة بغض النظر عن تاريخ تشغيل الـ seeder فعليًا.
     *
     * كل المرضى المزروعين يستخدمون كلمة المرور: patient123
     */
    public function run(): void
    {
        $patients = [
            ['name' => 'أحمد محمد العمري', 'email' => 'ahmed.omari@email.com', 'phone_whatsapp' => '+966501234567', 'birth_date' => '1990-05-12', 'country' => 'SA', 'operation_type' => 'زراعة الشعر', 'interest_level' => InterestLevel::VeryInterested, 'days_ago' => 250, 'operation_in' => null],
            ['name' => 'فاطمة علي الرشيد', 'email' => 'fatima.rashid@email.com', 'phone_whatsapp' => '+971501234567', 'birth_date' => '1988-09-03', 'country' => 'AE', 'operation_type' => 'تكميم المعدة', 'interest_level' => InterestLevel::VeryInterested, 'days_ago' => 230, 'operation_in' => null],
            ['name' => 'خالد سالم البلوشي', 'email' => 'khalid.balushi@email.com', 'phone_whatsapp' => '+96891234567', 'birth_date' => '1979-01-20', 'country' => 'OM', 'operation_type' => 'زراعة الأسنان', 'interest_level' => InterestLevel::Interested, 'days_ago' => 223, 'operation_in' => null],
            ['name' => 'نورا عبدالله القحطاني', 'email' => 'nora.qahtani@email.com', 'phone_whatsapp' => '+966507654321', 'birth_date' => '1995-07-22', 'country' => 'SA', 'operation_type' => 'تجميل الأنف (رينوبلاستي)', 'interest_level' => InterestLevel::Interested, 'days_ago' => 215, 'operation_in' => 10],
            ['name' => 'محمد حسن الدوسري', 'email' => 'm.dosari@email.com', 'phone_whatsapp' => '+97433123456', 'birth_date' => '1965-03-11', 'country' => 'QA', 'operation_type' => 'جراحة القلب', 'interest_level' => InterestLevel::FollowUp, 'days_ago' => 200, 'operation_in' => 8],
            ['name' => 'ليلى إبراهيم الشمري', 'email' => 'layla.shamri@email.com', 'phone_whatsapp' => '+96566123456', 'birth_date' => '1992-11-30', 'country' => 'KW', 'operation_type' => 'شفط الدهون', 'interest_level' => InterestLevel::FollowUp, 'days_ago' => 193, 'operation_in' => null],
            ['name' => 'عمر يوسف المنصوري', 'email' => 'omar.mansouri@email.com', 'phone_whatsapp' => '+971505678901', 'birth_date' => '1970-02-14', 'country' => 'AE', 'operation_type' => 'علاج الأورام', 'interest_level' => InterestLevel::NotInterested, 'days_ago' => 185, 'operation_in' => null],
            ['name' => 'سارة محمود التميمي', 'email' => 'sara.tamimi@email.com', 'phone_whatsapp' => '+9647801234567', 'birth_date' => '1983-06-09', 'country' => 'IQ', 'operation_type' => 'زراعة الكبد', 'interest_level' => InterestLevel::VeryInterested, 'days_ago' => 175, 'operation_in' => 3],
            ['name' => 'يوسف أحمد المصري', 'email' => 'youssef.masri@email.com', 'phone_whatsapp' => '+20101234567', 'birth_date' => '1991-12-01', 'country' => 'EG', 'operation_type' => 'جراحة العيون (ليزك)', 'interest_level' => InterestLevel::Interested, 'days_ago' => 168, 'operation_in' => null],
            ['name' => 'حنان سعيد الزهراني', 'email' => 'hanan.zahrani@email.com', 'phone_whatsapp' => '+966509876540', 'birth_date' => '1998-04-18', 'country' => 'SA', 'operation_type' => 'تقويم العظام', 'interest_level' => InterestLevel::NotInterested, 'days_ago' => 161, 'operation_in' => null],
            ['name' => 'ريم عبدالعزيز العتيبي', 'email' => 'reem.otaibi@email.com', 'phone_whatsapp' => '+966512345678', 'birth_date' => '1994-08-25', 'country' => 'SA', 'operation_type' => 'تكبير الثدي', 'interest_level' => InterestLevel::Interested, 'days_ago' => 90, 'operation_in' => null],
            ['name' => 'طارق سالم اليوسف', 'email' => 'tariq.yousef@email.com', 'phone_whatsapp' => '+97333445566', 'birth_date' => '1987-10-05', 'country' => 'BH', 'operation_type' => 'زراعة الأسنان', 'interest_level' => InterestLevel::FollowUp, 'days_ago' => 60, 'operation_in' => null],
        ];

        foreach ($patients as $data) {
            $registeredAt = Carbon::now()->subDays($data['days_ago']);

            $patient = Patient::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'patient123',
                    'phone_whatsapp' => $data['phone_whatsapp'],
                    'birth_date' => $data['birth_date'],
                    'country' => $data['country'],
                    'operation_type' => $data['operation_type'],
                    'interest_level' => $data['interest_level'],
                    'operation_date' => $data['operation_in'] !== null
                        ? Carbon::now()->addDays($data['operation_in'])->toDateString()
                        : null,
                ]
            );

            // نضبط created_at يدويًا ليعكس ترتيب التسجيل التاريخي (لا يمكن تمريره عبر create مباشرة)
            $patient->timestamps = false;
            $patient->forceFill(['created_at' => $registeredAt, 'updated_at' => $registeredAt])->save();
            $patient->timestamps = true;
        }
    }
}
