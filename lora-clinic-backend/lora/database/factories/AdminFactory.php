<?php

namespace Database\Factories;

use App\Enums\AdminRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    protected $model = \App\Models\Admin::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => AdminRole::Staff,
        ];
    }
}
