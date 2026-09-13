<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Patient;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentOverdueReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail', WhatsAppChannel::class];
    }

    public function toMail(Patient $notifiable): MailMessage
    {
        $remaining = number_format((float) $this->invoice->remaining_amount, 0);

        return (new MailMessage)
            ->subject("تذكير بدفعة متأخرة - فاتورة {$this->invoice->invoice_number}")
            ->greeting("مرحبًا {$notifiable->name}،")
            ->line("نود تذكيركم بأن الفاتورة رقم {$this->invoice->invoice_number} ({$this->invoice->operation_type}) قد تجاوزت تاريخ الاستحقاق.")
            ->line("المبلغ المتبقي: \${$remaining}")
            ->action('عرض الفاتورة', config('frontend.url'))
            ->line('نرجو التكرم بسداد المبلغ في أقرب وقت ممكن، أو التواصل معنا في حال وجود أي استفسار.');
    }

    public function toWhatsApp(Patient $notifiable): string
    {
        $remaining = number_format((float) $this->invoice->remaining_amount, 0);

        return "مرحبًا {$notifiable->name} ⚠️\n"
            ."فاتورتك رقم {$this->invoice->invoice_number} ({$this->invoice->operation_type}) متأخرة السداد.\n"
            ."المبلغ المتبقي: \${$remaining}\n"
            .'يُرجى التواصل معنا لترتيب السداد.';
    }
}
