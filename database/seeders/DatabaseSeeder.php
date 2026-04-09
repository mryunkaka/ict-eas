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
            'email' => 'ict.admin@easgroup.co.id',
        ], [
            'unit_id' => $eas->id,
            'name' => 'ICT Admin',
            'password' => 'password',
            'role' => UserRole::IctAdmin,
            'job_title' => 'ICT Support Lead',
            'is_active' => true,
        ]);

        User::updateOrCreate([
            'email' => 'hrga.approver@easgroup.co.id',
        ], [
            'unit_id' => $eas->id,
            'name' => 'HRGA Approver',
            'password' => 'password',
            'role' => UserRole::HrgaApprover,
            'job_title' => 'HRGA Officer',
            'is_active' => true,
        ]);

        User::updateOrCreate([
            'email' => 'unit.admin@easgroup.co.id',
        ], [
            'unit_id' => $unit->id,
            'name' => 'Unit Admin',
            'password' => 'password',
            'role' => UserRole::UnitAdmin,
            'job_title' => 'Kepala Unit',
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
