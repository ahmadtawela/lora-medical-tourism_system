<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Api\Admin\MedicalReportController as AdminMedicalReportController;
use App\Http\Controllers\Api\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Api\Admin\PatientController as AdminPatientController;
use App\Http\Controllers\Api\Auth\AdminAuthController;
use App\Http\Controllers\Api\Auth\PatientAuthController;
use App\Http\Controllers\Api\Auth\SetPasswordController;
use App\Http\Controllers\Api\Auth\UnifiedAuthController;
use App\Http\Controllers\Api\Patient\DashboardController as PatientDashboardController;
use App\Http\Controllers\Api\Patient\InvoiceController as PatientInvoiceController;
use App\Http\Controllers\Api\Patient\MedicalReportController as PatientMedicalReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\PatientImportController;

/*
|--------------------------------------------------------------------------
| تسجيل دخول موحّد - شاشة واحدة، النظام يحدد الدور تلقائيًا (أدمن أو مريض)
|--------------------------------------------------------------------------
| هذا المسار المُفضَّل للفرونت إند الحالي. مسارات /admin/login و /patient/login
| بالأسفل بقيت متاحة أيضًا لأغراض التوافق، وليست مطلوبة إن استُخدم هذا فقط.
*/
Route::post('login', [UnifiedAuthController::class, 'login'])->middleware('throttle:10,1')->name('login');
Route::post('logout', [UnifiedAuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

/*
|--------------------------------------------------------------------------
| تعيين كلمة المرور لأول مرة (رابط الدعوة المُرسَل عند إنشاء حساب مريض)
|--------------------------------------------------------------------------
| عام بلا مصادقة عمدًا - المريض لا يملك جلسة بعد. الحماية عبر التوكن
| المُشفَّر نفسه (صالح لمرة واحدة، لمدة 7 أيام) + throttle عام ضد التخمين.
*/
Route::post('patient/set-password', SetPasswordController::class)
    ->middleware('throttle:10,1')
    ->name('patient.set-password');

/*
|--------------------------------------------------------------------------
| بوابة الإدارة (لوحة الإدارة) - Lora Clinic Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // throttle:10,1 = حد أقصى 10 محاولات/دقيقة لكل IP (خط دفاع أول عام)،
    // بالإضافة إلى الحد الأدق (5 محاولات لكل بريد+IP) داخل AdminAuthController نفسه
    Route::post('login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1')->name('login');

    Route::middleware(['auth:sanctum', 'is.admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::apiResource('patients', AdminPatientController::class);
        Route::get('patients/import-template', [PatientImportController::class, 'downloadTemplate'])
    ->name('patients.import-template');
Route::post('patients/import', [PatientImportController::class, 'import'])
    ->name('patients.import');
        Route::patch('patients/{id}/restore', [AdminPatientController::class, 'restore'])->name('patients.restore');
        Route::post('patients/{patient}/resend-invite', [AdminPatientController::class, 'resendInvite'])
            ->name('patients.resend-invite');

        Route::apiResource('invoices', AdminInvoiceController::class);
        Route::patch('invoices/{id}/restore', [AdminInvoiceController::class, 'restore'])->name('invoices.restore');

        Route::apiResource('medical-reports', AdminMedicalReportController::class)
            ->only(['index', 'store', 'show', 'destroy']);
        Route::patch('medical-reports/{id}/restore', [AdminMedicalReportController::class, 'restore'])
            ->name('medical-reports.restore');
        Route::delete('medical-reports/{id}/force', [AdminMedicalReportController::class, 'forceDestroy'])
            ->name('medical-reports.force-destroy');

        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/mark-all-read', [AdminNotificationController::class, 'markAllAsRead'])
            ->name('notifications.mark-all-read');
        Route::patch('notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])
            ->name('notifications.mark-read');
    });
});

/*
|--------------------------------------------------------------------------
| بوابة المريض - Lora Clinic Patient Portal
|--------------------------------------------------------------------------
*/
Route::prefix('patient')->name('patient.')->group(function () {
    Route::post('login', [PatientAuthController::class, 'login'])->middleware('throttle:10,1')->name('login');

    Route::middleware(['auth:sanctum', 'is.patient'])->group(function () {
        Route::post('logout', [PatientAuthController::class, 'logout'])->name('logout');

        Route::get('dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');

        Route::get('invoices', [PatientInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [PatientInvoiceController::class, 'show'])->name('invoices.show');

        Route::get('medical-reports', [PatientMedicalReportController::class, 'index'])->name('reports.index');
        Route::post('medical-reports', [PatientMedicalReportController::class, 'store'])->name('reports.store');
    });
});
