<?php

namespace App\Notifications;

use App\Models\Patient;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpcomingOperationReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $daysUntil) {}

    public function via(object $notifiable): array
    {
        return ['mail', WhatsAppChannel::class];
    }

    public function toMail(Patient $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تذكير بموعد عمليتك القادمة - Lora Clinic')
            ->greeting("مرحبًا {$notifiable->name}،")
            ->line("نود تذكيركم بأن عملية \"{$notifiable->operation_type}\" مقررة بعد {$this->daysUntil} يوم/أيام، بتاريخ {$notifiable->operation_date?->toDateString()}.")
            ->line('يُرجى التأكد من استيفاء كل التحضيرات والفحوصات المطلوبة قبل الموعد.')
            ->action('عرض بوابتي', config('frontend.url'))
            ->line('لأي استفسار، فريقنا جاهز لمساعدتكم.');
    }

    public function toWhatsApp(Patient $notifiable): string
    {
        return "مرحبًا {$notifiable->name} 🗓️\n"
            ."تذكير: عملية \"{$notifiable->operation_type}\" مقررة بعد {$this->daysUntil} يوم/أيام ({$notifiable->operation_date?->toDateString()}).\n"
            .'تواصلوا معنا لأي استفسار حول التحضيرات.';
    }
}
