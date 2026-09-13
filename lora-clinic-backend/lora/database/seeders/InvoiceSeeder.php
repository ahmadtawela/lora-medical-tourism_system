<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class InvoiceSeeder extends Seeder
{
    /**
     * تواريخ الاستحقاق نسبية لتاريخ التشغيل: فاتورة واحدة فقط (INV-2025-003) لها
     * تاريخ استحقاق في الماضي مع رصيد متبقٍ > 0 لتبقى الحالة "متأخرة" الوحيدة،
     * تمامًا كما هو ظاهر في لقطات الشاشة (فاتورة متأخرة واحدة).
     */
    public function run(): void
    {
        $invoices = [
            [
                'email' => 'ahmed.omari@email.com', 'number' => 'INV-2025-001', 'operation_type' => 'زراعة الشعر',
                'total' => 3500, 'cost' => 1800, 'paid' => 3500, 'status' => InvoiceStatus::Paid,
                'issue_in' => -210, 'due_in' => -180,
            ],
            [
                'email' => 'fatima.rashid@email.com', 'number' => 'INV-2025-002', 'operation_type' => 'تكميم المعدة',
                'total' => 8500, 'cost' => 4200, 'paid' => 5000, 'status' => InvoiceStatus::Partial,
                'issue_in' => -20, 'due_in' => 14,
            ],
            [
                'email' => 'khalid.balushi@email.com', 'number' => 'INV-2025-003', 'operation_type' => 'زراعة الأسنان',
                'total' => 4200, 'cost' => 2100, 'paid' => 0, 'status' => InvoiceStatus::Overdue,
                'issue_in' => -40, 'due_in' => -10,
            ],
            [
                'email' => 'nora.qahtani@email.com', 'number' => 'INV-2025-004', 'operation_type' => 'تجميل الأنف (رينوبلاستي)',
                'total' => 5800, 'cost' => 2900, 'paid' => 2000, 'status' => InvoiceStatus::Partial,
                'issue_in' => -15, 'due_in' => 45,
            ],
            [
                'email' => 'm.dosari@email.com', 'number' => 'INV-2025-005', 'operation_type' => 'جراحة القلب',
                'total' => 22000, 'cost' => 14000, 'paid' => 0, 'status' => InvoiceStatus::Pending,
                'issue_in' => -5, 'due_in' => 60,
            ],
            [
                'email' => 'sara.tamimi@email.com', 'number' => 'INV-2025-006', 'operation_type' => 'زراعة الكبد',
                'total' => 35000, 'cost' => 21000, 'paid' => 15000, 'status' => InvoiceStatus::Partial,
                'issue_in' => -25, 'due_in' => 75,
            ],
            [
                'email' => 'youssef.masri@email.com', 'number' => 'INV-2025-007', 'operation_type' => 'جراحة العيون (ليزك)',
                'total' => 2800, 'cost' => 1200, 'paid' => 2800, 'status' => InvoiceStatus::Paid,
                'issue_in' => -170, 'due_in' => -150,
            ],
        ];

        foreach ($invoices as $data) {
            $patient = Patient::where('email', $data['email'])->first();

            if (! $patient) {
                continue;
            }

            Invoice::updateOrCreate(
                ['invoice_number' => $data['number']],
                [
                    'patient_id' => $patient->id,
                    'operation_type' => $data['operation_type'],
                    'total_amount' => $data['total'],
                    'cost_amount' => $data['cost'],
                    'paid_amount' => $data['paid'],
                    'status' => $data['status'],
                    'issue_date' => Carbon::now()->addDays($data['issue_in'])->toDateString(),
                    'due_date' => Carbon::now()->addDays($data['due_in'])->toDateString(),
                ]
            );
        }
    }
}
