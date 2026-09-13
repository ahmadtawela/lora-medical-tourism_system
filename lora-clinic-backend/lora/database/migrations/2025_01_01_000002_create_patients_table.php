<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            // password: nullable الآن - المريض يعيّنها بنفسه عبر رابط دعوة (لا يولّدها
            // النظام ولا يراها الأدمن إطلاقًا). راجع password_setup_token أدناه.
            $table->string('password')->nullable();
            // توكن دعوة "تعيين كلمة المرور" - يُخزَّن مُشفَّرًا (hash)، ونستخدمه لمرة واحدة
            // بصلاحية محدودة، بنفس فلسفة password_reset_tokens القياسية في Laravel.
            $table->string('password_setup_token')->nullable();
            $table->timestamp('password_setup_expires_at')->nullable();
            $table->string('phone_whatsapp'); // رقم الواتساب
            // birth_date: موجود في نموذج "إضافة مريض جديد" وكان مفقودًا في المخطط الأصلي
            $table->date('birth_date')->nullable();
            $table->string('country', 2); // كود الدولة (SA, AE, KW, QA, OM, IQ, EG ...)
            $table->string('operation_type'); // نوع العملية
            $table->string('interest_level')->default('interested'); // App\Enums\InterestLevel
            // operation_date: تاريخ العملية المقررة - أضيف لدعم تنبيهات "عمليات قادمة"
            // (غير موجود في المخطط اليدوي الأصلي لكنه ضروري لتشغيل هذه الميزة الظاهرة في الواجهة)
            $table->date('operation_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps(); // created_at = تاريخ التسجيل الظاهر في الجدول
            // حذف ناعم: "حذف" مريض يخفيه فقط، ولا يحذف فواتيره/تقاريره فعليًا
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
