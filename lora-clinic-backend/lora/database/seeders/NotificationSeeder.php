<?php

namespace Database\Seeders;

use App\Enums\NotificationType;
use App\Models\Invoice;
use App\Models\MedicalReport;
use App\Models\Notification;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $sara = Patient::where('email', 'sara.tamimi@email.com')->first();
        $khalid = Patient::where('email', 'khalid.balushi@email.com')->first();
        $mohammed = Patient::where('email', 'm.dosari@email.com')->first();
        $nora = Patient::where('email', 'nora.qahtani@email.com')->first();
        $fatima = Patient::where('email', 'fatima.rashid@email.com')->first();

        $invoice003 = Invoice::where('invoice_number', 'INV-2025-003')->first();
        $invoice002 = Invoice::where('invoice_number', 'INV-2025-002')->first();
        $noraReport = MedicalReport::where('patient_id', $nora?->id)->latest()->first();

        $rows = [
            [
                'type' => NotificationType::UpcomingOperation,
                'title' => "عملية زراعة الكبد لـ {$sara?->name} مقررة بعد 3 أيام",
                'patient' => $sara, 'invoice' => null, 'report' => null,
                'event_in_days' => 3, 'is_read' => false, 'created_days_ago' => 1,
            ],
            [
                'type' => NotificationType::PaymentOverdue,
                'title' => "فاتورة {$invoice003?->invoice_number} متأخرة - {$khalid?->name} لم يسدد بعد",
                'patient' => $khalid, 'invoice' => $invoice003, 'report' => null,
                'event_in_days' => -10, 'is_read' => false, 'created_days_ago' => 2,
            ],
            [
                'type' => NotificationType::UpcomingOperation,
                'title' => "عملية جراحة القلب لـ {$mohammed?->name} مقررة بعد 8 أيام",
                'patient' => $mohammed, 'invoice' => null, 'report' => null,
                'event_in_days' => 8, 'is_read' => false, 'created_days_ago' => 3,
            ],
            [
                'type' => NotificationType::NewReport,
                'title' => "رفع تقرير طبي جديد - {$nora?->name}",
                'patient' => $nora, 'invoice' => null, 'report' => $noraReport,
                'event_in_days' => -6, 'is_read' => true, 'created_days_ago' => 6,
            ],
            [
                'type' => NotificationType::PaymentOverdue,
                'title' => "الدفعة المتبقية لـ {$fatima?->name} (\${$this->fmt($invoice002?->remaining_amount)}) لم تُسدَّد في الموعد",
                'patient' => $fatima, 'invoice' => $invoice002, 'report' => null,
                'event_in_days' => -2, 'is_read' => false, 'created_days_ago' => 2,
            ],
            [
                'type' => NotificationType::UpcomingOperation,
                'title' => "تجميل الأنف لـ {$nora?->name} مقررة بعد 10 أيام",
                'patient' => $nora, 'invoice' => null, 'report' => null,
                'event_in_days' => 10, 'is_read' => true, 'created_days_ago' => 4,
            ],
        ];

        foreach ($rows as $row) {
            if (! $row['patient']) {
                continue;
            }

            $notification = Notification::updateOrCreate(
                ['type' => $row['type']->value, 'related_patient_id' => $row['patient']->id, 'title' => $row['title']],
                [
                    'related_invoice_id' => $row['invoice']?->id,
                    'related_report_id' => $row['report']?->id,
                    'event_date' => Carbon::now()->addDays($row['event_in_days'])->toDateString(),
                    'is_read' => $row['is_read'],
                ]
            );

            $createdAt = Carbon::now()->subDays($row['created_days_ago']);
            $notification->timestamps = false;
            $notification->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
            $notification->timestamps = true;
        }
    }

    private function fmt(null|int|float|string $amount): string
    {
        return number_format((float) $amount, 0);
    }
}
