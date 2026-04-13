<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IctRequestQuotation extends Model
{
    protected $fillable = [
        'ict_request_id',
        'ict_request_item_id',
        'line_number',
        'vendor_name',
        'attachment_name',
        'attachment_path',
        'attachment_size',
        'attachment_mime',
    ];

    public function ictRequest(): BelongsTo
    {
        return $this->belongsTo(IctRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(IctRequestItem::class, 'ict_request_item_id');
    }
}
