<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
            'job_title' => 'Manager ICT',
            'is_active' => true,
        ]);

        User::updateOrCreate([
            'email' => 'admin.ict@easgroup.co.id',
        ], [
            'unit_id' => $eas->id,
            'name' => 'Admin ICT',
            'password' => Hash::make('password'),
            'role' => UserRole::AdminIct,
            'job_title' => 'Admin ICT',
            'is_active' => true,
        ]);

        User::updateOrCreate([
            'email' => 'staff.ict@easgroup.co.id',
        ], [
            'unit_id' => $eas->id,
            'name' => 'Staff ICT',
            'password' => Hash::make('password'),
            'role' => UserRole::StaffIct,
            'job_title' => 'Staff ICT',
            'is_active' => true,
        ]);

        User::updateOrCreate([
            'email' => 'asmen.ict@easgroup.co.id',
        ], [
            'unit_id' => $eas->id,
            'name' => 'Asmen ICT',
            'password' => Hash::make('password'),
            'role' => UserRole::AsmenIct,
            'job_title' => 'Asisten Manager ICT',
            'is_active' => true,
        ]);

        User::updateOrCreate([
            'email' => 'manager.ict@easgroup.co.id',
        ], [
            'unit_id' => $eas->id,
            'name' => 'Manager ICT',
            'password' => Hash::make('password'),
            'role' => UserRole::ManagerIct,
            'job_title' => 'Manager ICT',
            'is_active' => true,
        ]);
    }
}
