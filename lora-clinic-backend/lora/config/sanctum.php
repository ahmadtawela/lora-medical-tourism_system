<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | مطلوب فقط إن كان الفرونت إند (React/Vue SPA) على نفس النطاق ويريد
    | مصادقة عبر الكوكيز بدل التوكن. نظامنا الحالي يعتمد على Bearer Token
    | (Authorization header) وهو يعمل بغض النظر عن هذا الإعداد.
    |
    */
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort()
    ))),

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | null = التوكنات لا تنتهي صلاحيتها أبدًا إلا عند حذفها يدويًا (logout).
    | يمكن ضبطها بالدقائق (مثلاً 43200 = 30 يومًا) حسب سياسة الأمان المطلوبة.
    |
    */
    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
