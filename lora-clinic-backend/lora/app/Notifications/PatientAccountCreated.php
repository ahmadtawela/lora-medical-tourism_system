<?php

namespace App\Notifications;

use App\Models\Patient;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * تُرسَل مرة واحدة عند إضافة مريض جديد من لوحة الإدارة. تحمل رابط دعوة آمنًا
 * لتعيين كلمة المرور بنفسه (ولا تحمل أي كلمة مرور فعلية أبدًا - لا الأدمن
 * ولا الباك إند يريان كلمة مرور المريض في أي مرحلة).
 */
class PatientAccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $plainSetupToken) {}

    public function via(object $notifiable): array
    {
        return ['mail', WhatsAppChannel::class];
    }

    private function setupUrl(Patient $notifiable): string
    {
        return sprintf(
            '%s/set-password?token=%s&email=%s',
            rtrim(config('frontend.url'), '/'),
            $this->plainSetupToken,
            urlencode($notifiable->email)
        );
    }

    public function toMail(Patient $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('مرحبًا بك في Lora Clinic - فعّل حسابك')
            ->greeting("أهلًا {$notifiable->name}،")
            ->line('تم إنشاء حسابك في بوابة المريض الخاصة بـ Lora Clinic.')
            ->line('لتفعيل حسابك، اضغط الزر أدناه لتعيين كلمة مرورك الخاصة (الرابط صالح لمدة 7 أيام).')
            ->action('تعيين كلمة المرور', $this->setupUrl($notifiable))
            ->line('إن لم تطلب إنشاء هذا الحساب، يمكنك تجاهل هذه الرسالة بأمان.');
    }

    public function toWhatsApp(Patient $notifiable): string
    {
        return "مرحبًا {$notifiable->name} 👋\n"
            ."تم إنشاء حسابك في بوابة مريض Lora Clinic.\n"
            ."لتفعيله، عيّن كلمة مرورك من هنا (صالح 7 أيام):\n"
            .$this->setupUrl($notifiable);
    }
}
