<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IctRequestReviewHistory extends Model
{
    protected $fillable = [
        'ict_request_id',
        'action',
        'note',
        'attachment_name',
        'attachment_path',
        'attachment_size',
        'attachment_mime',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'attachment_size' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function ictRequest(): BelongsTo
    {
        return $this->belongsTo(IctRequest::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
