<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique(); // مثال: INV-2025-001
            $table->string('operation_type');
            $table->decimal('total_amount', 12, 2);   // المبلغ الإجمالي
            $table->decimal('cost_amount', 12, 2);    // سعر التكلفة (تكلفة العملية الفعلية على العيادة)
            $table->decimal('paid_amount', 12, 2)->default(0); // المبلغ المدفوع
            // remaining_amount: عمود مشتق يُحسب تلقائيًا (total - paid) عبر حدث saving في الموديل،
            // وليس حقل إدخال في النموذج (لاحظ عدم وجوده في نموذج "إنشاء فاتورة جديدة")
            $table->decimal('remaining_amount', 12, 2)->default(0);
            $table->string('status')->default('pending'); // App\Enums\InvoiceStatus
            $table->date('issue_date')->default(now()); // تاريخ الإصدار
            $table->date('due_date'); // تاريخ الاستحقاق
            $table->timestamps();
            $table->softDeletes(); // سجل مالي - لا يُحذف نهائيًا عبر واجهة الإدارة

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
