<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            // diagnosis_name: يقابل حقل "اسم المرض / التشخيص" في نموذج الرفع
            // (كان مسمى report_name في المخطط اليدوي، أُعيدت تسميته ليطابق الواجهة فعليًا)
            $table->string('diagnosis_name');
            $table->string('file_path');    // مسار التخزين الفعلي على القرص
            $table->string('file_name');    // اسم الملف الأصلي للعرض، مثل تقرير_فحص_الشعر_أحمد.pdf
            $table->string('file_type', 10); // pdf | image - يحدد الأيقونة المعروضة في الكارد
            $table->text('description')->nullable(); // الملخص القصير الظاهر أسفل كل كارد
            $table->timestamps(); // created_at = تاريخ الرفع
            $table->softDeletes(); // سجل طبي - لا يُحذف نهائيًا عبر واجهة الإدارة
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_reports');
    }
};
