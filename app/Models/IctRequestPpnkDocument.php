<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IctRequestPpnkDocument extends Model
{
    protected $fillable = [
        'ict_request_id',
        'ppnk_number',
        'attachment_name',
        'attachment_path',
        'attachment_size',
        'attachment_mime',
        'uploaded_by',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'attachment_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function ictRequest(): BelongsTo
    {
        return $this->belongsTo(IctRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(IctRequestItem::class, 'ppnk_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
