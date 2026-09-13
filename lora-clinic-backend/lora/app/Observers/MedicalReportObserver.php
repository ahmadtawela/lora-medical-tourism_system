<?php

namespace App\Observers;

use App\Enums\NotificationType;
use App\Models\MedicalReport;
use App\Models\Notification;
use App\Notifications\NewMedicalReportUploaded;

class MedicalReportObserver
{
    /**
     * ينشئ تنبيه "تقرير جديد" في لوحة الإدارة، ويرسل إشعارًا فعليًا
     * (بريد + واتساب) للمريض، بغض النظر عمن رفع التقرير
     * (الأدمن من لوحة التحكم، أو المريض نفسه من البوابة).
     */
    public function created(MedicalReport $report): void
    {
        Notification::create([
            'type' => NotificationType::NewReport,
            'title' => "رفع تقرير طبي جديد - {$report->patient->name}",
            'related_patient_id' => $report->patient_id,
            'related_report_id' => $report->id,
            'event_date' => now()->toDateString(),
        ]);

        $report->patient->notify(new NewMedicalReportUploaded($report));
    }
}
