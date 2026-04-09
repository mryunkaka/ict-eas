<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case IctAdmin = 'ict_admin';
    case UnitAdmin = 'unit_admin';
    case UnitUser = 'unit_user';
    case HrgaApprover = 'hrga_approver';
}
