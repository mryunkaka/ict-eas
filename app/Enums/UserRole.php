<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case UnitUser = 'unit_user';
    case AdminIct = 'admin_ict';
    case StaffIct = 'staff_ict';
    case AsmenIct = 'asmen_ict';
    case ManagerIct = 'manager_ict';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::UnitUser => 'Unit User',
            self::AdminIct => 'Admin ICT',
            self::StaffIct => 'Staff ICT',
            self::AsmenIct => 'Asmen ICT',
            self::ManagerIct => 'Manager ICT',
        };
    }
}
