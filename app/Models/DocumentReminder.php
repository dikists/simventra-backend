<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentReminder extends Model
{
    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'document_type',
        'document_title',
        'related_name',
        'expiry_date',
        'days_before',
        'channel',
        'status',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'sent_at'     => 'datetime',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
