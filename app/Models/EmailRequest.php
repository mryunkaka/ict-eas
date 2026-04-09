<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailRequest extends Model
{
    protected $fillable = [
        'unit_id',
        'requester_id',
        'employee_name',
        'department_name',
        'job_title',
        'requested_email',
        'access_level',
        'justification',
        'status',
        'manager_approved_by',
        'hrga_verified_by',
        'ict_processed_by',
        'manager_approved_at',
        'hrga_verified_at',
        'ict_processed_at',
    ];

    protected function casts(): array
    {
        return [
            'manager_approved_at' => 'datetime',
            'hrga_verified_at' => 'datetime',
            'ict_processed_at' => 'datetime',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
