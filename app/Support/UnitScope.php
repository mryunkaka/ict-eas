<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UnitScope
{
    public static function apply(Builder $query, User $user, string $column = 'unit_id'): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where($column, $user->unit_id);
    }
}
