<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    protected $fillable = [
        'unit_id',
        'assigned_user_id',
        'uuid',
        'asset_number',
        'category',
        'name',
        'brand',
        'model',
        'serial_number',
        'vendor',
        'specification',
        'location',
        'purchase_date',
        'warranty_until',
        'condition_status',
        'lifecycle_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'specification' => 'array',
            'purchase_date' => 'date',
            'warranty_until' => 'date',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
