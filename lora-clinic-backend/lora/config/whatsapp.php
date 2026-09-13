<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Cloud API (Meta) Configuration
    |--------------------------------------------------------------------------
    |
    | نستخدم واجهة WhatsApp Cloud API الرسمية من Meta لإرسال رسائل حقيقية.
    | يتطلب هذا حساب Meta Business + رقم واتساب أعمال معتمد. الخطوات:
    |
    | 1. أنشئ تطبيقًا على https://developers.facebook.com/apps
    | 2. أضف منتج "WhatsApp" للتطبيق
    | 3. من "WhatsApp > API Setup" احصل على: Phone Number ID + Temporary Access Token
    | 4. للاستخدام في الإنتاج (توكن دائم): أنشئ System User Token عبر Meta Business Suite
    | 5. للإرسال لأي رقم (وليس فقط أرقام الاختبار المسجَّلة)، يتطلب مراجعة (App Review)
    |    من Meta والانتقال بالتطبيق لوضع "Live"
    |
    | بدون هذه القيم، سيسجّل WhatsAppService الرسالة في اللوق (log) فقط
    | بدل إرسالها فعليًا - مفيد أثناء التطوير المحلي دون حساب Meta حقيقي.
    |
    */

    'enabled' => env('WHATSAPP_ENABLED', false),

    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),

    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),

    'api_version' => env('WHATSAPP_API_VERSION', 'v20.0'),

    'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),

];
