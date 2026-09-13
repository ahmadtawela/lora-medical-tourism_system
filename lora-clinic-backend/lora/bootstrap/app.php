<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsPatient;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Sanctum يخزّن توكنات الأدمن والمريض في نفس الجدول (polymorphic)،
        // لذلك نستخدم هذين الـ alias للتمييز بينهما على مستوى الراوت
        $middleware->alias([
            'is.admin' => EnsureUserIsAdmin::class,
            'is.patient' => EnsureUserIsPatient::class,
        ]);
        // ملاحظة: لا نستخدم $middleware->statefulApi() هنا عمدًا - نظامنا
        // بالكامل يعتمد على Bearer Token في الهيدر (auth:sanctum + PAT)،
        // وليس على كوكيز/جلسات SPA. تفعيلها يفرض حماية CSRF على /api/*
        // ويسبب خطأ 419 على كل طلب (بما فيها تسجيل الدخول نفسه).
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
