<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * إرسال رسائل واتساب حقيقية عبر WhatsApp Cloud API الرسمية من Meta.
 *
 * يتطلب ضبط WHATSAPP_ENABLED=true و WHATSAPP_PHONE_NUMBER_ID و WHATSAPP_ACCESS_TOKEN
 * في ملف .env (راجع config/whatsapp.php لتفاصيل الحصول عليها).
 *
 * ملاحظة مهمة حول "الرسائل الحرة": واجهة Meta لا تسمح بإرسال نص حر خارج
 * نافذة الـ 24 ساعة من آخر رسالة أرسلها العميل، إلا عبر "قوالب رسائل"
 * (Message Templates) معتمدة مسبقًا من Meta. للاستخدام الفعلي في الإنتاج
 * (تنبيهات دفع، عمليات، إلخ) يجب إنشاء قوالب مطابقة عبر WhatsApp Manager
 * واستبدال sendText() أدناه بـ sendTemplate() حسب القالب المعتمد.
 */
class WhatsAppService
{
    public function sendText(string $to, string $message): bool
    {
        $to = $this->normalizePhone($to);

        if (! config('whatsapp.enabled') || ! config('whatsapp.phone_number_id') || ! config('whatsapp.access_token')) {
            Log::info('[WhatsApp:disabled] رسالة لم تُرسَل فعليًا (الخدمة غير مُفعّلة/غير مُهيّأة)', [
                'to' => $to,
                'message' => $message,
            ]);

            return false;
        }

        $url = sprintf(
            '%s/%s/%s/messages',
            rtrim(config('whatsapp.base_url'), '/'),
            config('whatsapp.api_version'),
            config('whatsapp.phone_number_id')
        );

        try {
            $response = Http::withToken(config('whatsapp.access_token'))
                ->timeout(10)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $message],
                ]);

            if ($response->failed()) {
                Log::error('[WhatsApp] فشل الإرسال', ['to' => $to, 'response' => $response->json()]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[WhatsApp] استثناء أثناء الإرسال', ['to' => $to, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /** واجهة Meta تتوقع الرقم بصيغة دولية بدون '+' أو مسافات (مثال: 966501234567) */
    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^\d]/', '', $phone);
    }
}
