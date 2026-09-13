<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@loraclinic.com'],
            [
                'name' => 'مدير النظام',
                'password' => 'admin1234', // hashed تلقائيًا عبر cast 'hashed' في الموديل
                'role' => AdminRole::SuperAdmin,
            ]
        );

    
    }
}
