<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentFollowup extends Model
{
    protected $fillable = [
        'incident_id',
        'user_id',
        'action_taken',
        'status_change',
        'attachment',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
