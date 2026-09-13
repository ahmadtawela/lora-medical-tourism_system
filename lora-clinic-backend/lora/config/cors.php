<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | ضروري لأن الفرونت إند يعمل على origin مختلف (منفذ/نطاق مختلف) عن هذا الـ API.
    | بدون هذا الإعداد، سيمنع المتصفح (وليس الخادم) طلبات الفرونت إند تلقائيًا.
    |
    | للتطوير المحلي: القيمة الافتراضية '*' تسمح بأي origin - يعمل مباشرة بدون تعديل.
    | للإنتاج: اضبط CORS_ALLOWED_ORIGINS في .env بقائمة نطاقات مفصولة بفواصل،
    | مثال: CORS_ALLOWED_ORIGINS=https://app.loraclinic.com,https://admin.loraclinic.com
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '*'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // false لأننا نعتمد Bearer Token في الهيدر وليس كوكيز/جلسات - لا حاجة لإرسال credentials
    'supports_credentials' => false,

];
