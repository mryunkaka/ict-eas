<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $eas = Unit::firstOrCreate(
            ['code' => 'EAS-HO'],
            ['name' => 'EAS Head Office', 'type' => 'eas', 'is_head_office' => true, 'is_active' => true]
        );

        $unit = Unit::firstOrCreate(
            ['code' => 'UNIT-01'],
            ['name' => 'Unit Contoh', 'type' => 'unit', 'is_head_office' => false, 'is_active' => true]
        );

        User::updateOrCreate([
            'email' => 'superadmin@easgroup.co.id',
        ], [
            'unit_id' => $eas->id,
            'name' => 'Super Admin EAS',
            'password' => 'password',
            'role' => UserRole::SuperAdmin,
            'job_title' => 'Manager ICT',
            'is_active' => true,
        ]);

        User::updateOrCreate([
            'email' => 'user.unit@easgroup.co.id',
        ], [
            'unit_id' => $unit->id,
            'name' => 'User Unit',
            'password' => 'password',
            'role' => UserRole::UnitUser,
            'job_title' => 'Staff',
            'is_active' => true,
        ]);
    }
}
