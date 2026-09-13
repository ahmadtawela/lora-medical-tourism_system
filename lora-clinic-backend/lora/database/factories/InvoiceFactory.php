<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = \App\Models\Invoice::class;

    public function definition(): array
    {
        $total = fake()->randomFloat(2, 1000, 30000);

        return [
            'patient_id' => Patient::factory(),
            'operation_type' => fake()->randomElement(['زراعة الشعر', 'تكميم المعدة', 'زراعة الأسنان']),
            'total_amount' => $total,
            'cost_amount' => $total * 0.6,
            'paid_amount' => 0,
            'status' => InvoiceStatus::Pending,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ];
    }
}
