<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case IctAdmin = 'ict_admin';
    case UnitAdmin = 'unit_admin';
    case UnitUser = 'unit_user';
    case HrgaApprover = 'hrga_approver';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::IctAdmin => 'ICT Admin',
            self::UnitAdmin => 'Unit Admin',
            self::UnitUser => 'Unit User',
            self::HrgaApprover => 'HRGA Approver',
        };
    }
}
