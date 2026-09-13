<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalReportFactory extends Factory
{
    protected $model = \App\Models\MedicalReport::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'diagnosis_name' => fake()->sentence(3),
            'file_path' => 'reports/placeholder.pdf',
            'file_name' => 'placeholder.pdf',
            'file_type' => 'pdf',
            'description' => fake()->sentence(),
        ];
    }
}
