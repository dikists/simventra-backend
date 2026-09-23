<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewActionItem extends Model
{
    protected $fillable = [
        'review_id',
        'action',
        'assigned_to',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function review()
    {
        return $this->belongsTo(ManagementReview::class, 'review_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
