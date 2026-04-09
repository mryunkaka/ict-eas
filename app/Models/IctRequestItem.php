<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IctRequestItem extends Model
{
    protected $fillable = [
        'ict_request_id',
        'line_number',
        'item_name',
        'brand_type',
        'quantity',
        'estimated_price',
        'notes',
    ];

    public function ictRequest(): BelongsTo
    {
        return $this->belongsTo(IctRequest::class);
    }
}
