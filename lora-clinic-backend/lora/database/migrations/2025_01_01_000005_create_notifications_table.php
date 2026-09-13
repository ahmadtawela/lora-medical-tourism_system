<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول التنبيهات - غير موجود إطلاقًا في المخطط اليدوي الأصلي رغم وجود
     * صفحة "التنبيهات" كاملة في لوحة الإدارة بأربعة تصنيفات.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // App\Enums\NotificationType
            $table->string('title'); // نص التنبيه الرئيسي
            $table->foreignId('related_patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('related_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('related_report_id')->nullable()->constrained('medical_reports')->nullOnDelete();
            // event_date: تاريخ الحدث المرتبط (موعد العملية، تاريخ استحقاق الدفعة...)
            // يختلف عن created_at الذي يمثل وقت توليد التنبيه نفسه
            $table->date('event_date')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['type', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
