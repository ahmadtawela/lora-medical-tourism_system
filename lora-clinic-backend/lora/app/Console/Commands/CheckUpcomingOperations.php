<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\Patient;
use App\Notifications\UpcomingOperationReminder;
use Illuminate\Console\Command;

/**
 * يبحث يوميًا عن المرضى الذين موعد عمليتهم (operation_date) خلال الأيام السبعة القادمة
 * وينشئ تنبيه "عملية قادمة" لهم (مرة واحدة فقط لكل مريض/تاريخ).
 */
class CheckUpcomingOperations extends Command
{
    protected $signature = 'notifications:check-upcoming-operations {--days=7 : عدد الأيام المستقبلية التي يجب التنبيه لها}';

    protected $description = 'إنشاء تنبيهات للعمليات المقررة خلال الأيام القادمة';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $patients = Patient::whereNotNull('operation_date')
            ->whereBetween('operation_date', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->get();

        $created = 0;

        foreach ($patients as $patient) {
            $alreadyNotified = Notification::where('related_patient_id', $patient->id)
                ->where('type', NotificationType::UpcomingOperation->value)
                ->whereDate('event_date', $patient->operation_date)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $daysUntil = (int) now()->startOfDay()->diffInDays($patient->operation_date, false);
            $whenText = match (true) {
                $daysUntil === 0 => 'اليوم',
                $daysUntil === 1 => 'غدًا',
                default => "بعد {$daysUntil} أيام",
            };

            Notification::create([
                'type' => NotificationType::UpcomingOperation,
                'title' => "عملية {$patient->operation_type} لـ {$patient->name} مقررة {$whenText}",
                'related_patient_id' => $patient->id,
                'event_date' => $patient->operation_date,
            ]);

            $patient->notify(new UpcomingOperationReminder($daysUntil));
            $created++;
        }

        $this->info("تم فحص العمليات القادمة، عدد التنبيهات الجديدة: {$created}");

        return self::SUCCESS;
    }
}
