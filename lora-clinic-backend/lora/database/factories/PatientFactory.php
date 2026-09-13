<?php

namespace Database\Factories;

use App\Enums\InterestLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = \App\Models\Patient::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'phone_whatsapp' => '+9665'.fake()->numerify('########'),
            'birth_date' => fake()->date(),
            'country' => fake()->randomElement(['SA', 'AE', 'KW', 'QA', 'OM', 'IQ', 'EG', 'BH']),
            'operation_type' => fake()->randomElement(['زراعة الشعر', 'تكميم المعدة', 'زراعة الأسنان']),
            'interest_level' => fake()->randomElement(InterestLevel::cases()),
            'operation_date' => null,
            'note' => null,
        ];
    }
}
