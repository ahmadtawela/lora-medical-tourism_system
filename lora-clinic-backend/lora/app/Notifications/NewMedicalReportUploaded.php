<?php

namespace App\Notifications;

use App\Models\MedicalReport;
use App\Models\Patient;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMedicalReportUploaded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly MedicalReport $report) {}

    public function via(object $notifiable): array
    {
        return ['mail', WhatsAppChannel::class];
    }

    public function toMail(Patient $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تقرير طبي جديد في ملفك - Lora Clinic')
            ->greeting("مرحبًا {$notifiable->name}،")
            ->line("تم رفع تقرير طبي جديد في ملفك: \"{$this->report->diagnosis_name}\".")
            ->when($this->report->description, fn ($mail) => $mail->line($this->report->description))
            ->action('عرض التقرير في بوابتي', config('frontend.url'))
            ->line('يمكنك مراجعته في أي وقت من قسم "تقاريري".');
    }

    public function toWhatsApp(Patient $notifiable): string
    {
        return "مرحبًا {$notifiable->name} 📋\n"
            ."تم رفع تقرير طبي جديد في ملفك: {$this->report->diagnosis_name}.\n"
            .'يمكنك الاطلاع عليه من بوابة المريض، قسم "تقاريري".';
    }
}
