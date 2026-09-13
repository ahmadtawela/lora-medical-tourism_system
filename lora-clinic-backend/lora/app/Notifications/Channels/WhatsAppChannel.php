<?php

namespace App\Notifications\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function __construct(private readonly WhatsAppService $whatsApp) {}

    /**
     * يُستدعى تلقائيًا عبر Laravel عندما تتضمن via() القيمة WhatsAppChannel::class.
     * يتوقع من كل Notification تعريف دالة toWhatsApp() تُرجع نص الرسالة.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $to = $notifiable->routeNotificationForWhatsApp($notification) ?? null;

        if (! $to) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        $this->whatsApp->sendText($to, $message);
    }
}
