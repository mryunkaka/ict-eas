<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IctRequest extends Model
{
    protected $fillable = [
        'unit_id',
        'requester_id',
        'form_number',
        'subject',
        'request_category',
        'priority',
        'status',
        'needed_at',
        'justification',
        'additional_budget_reason',
        'manager_approved_by',
        'ga_approved_by',
        'ict_approved_by',
        'manager_approved_at',
        'ga_approved_at',
        'ict_approved_at',
    ];

    protected function casts(): array
    {
        return [
            'needed_at' => 'date',
            'manager_approved_at' => 'datetime',
            'ga_approved_at' => 'datetime',
            'ict_approved_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(IctRequestItem::class);
    }
}
