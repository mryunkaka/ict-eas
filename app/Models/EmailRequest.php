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
        'diketahui_dept_head_name',
        'diketahui_dept_head_title',
        'diketahui_div_head_name',
        'diketahui_div_head_title',
        'disetujui_hrga_head_name',
        'disetujui_hrga_head_title',
        'pelaksana_ict_head_name',
        'pelaksana_ict_head_title',
        'status',
        'manager_approved_by',
        'hrga_verified_by',
        'ict_processed_by',
        'manager_approved_at',
        'hrga_verified_at',
        'ict_processed_at',
        'full_ttd_signed_at',
        'full_ttd_path',
        'full_ttd_name',
        'full_ttd_size',
        'full_ttd_mime',
    ];

    protected function casts(): array
    {
        return [
            'manager_approved_at' => 'datetime',
            'hrga_verified_at' => 'datetime',
            'ict_processed_at' => 'datetime',
            'full_ttd_signed_at' => 'datetime',
            'full_ttd_size' => 'integer',
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
