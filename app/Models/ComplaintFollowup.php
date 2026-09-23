<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintFollowup extends Model
{
    protected $fillable = [
        'complaint_id',
        'user_id',
        'action_taken',
        'status_change',
    ];

    public function complaint()
    {
        return $this->belongsTo(CustomerComplaint::class, 'complaint_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
