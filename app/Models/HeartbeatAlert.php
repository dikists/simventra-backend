<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeartbeatAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'driver_id',
        'vehicle_id',
        'last_ping_at',
        'silence_minutes',
        'push_sent_driver',
        'push_sent_dispatcher',
        'wa_sent',
        'error_notes',
    ];

    protected $casts = [
        'last_ping_at'          => 'datetime',
        'push_sent_driver'      => 'boolean',
        'push_sent_dispatcher'  => 'boolean',
        'wa_sent'               => 'boolean',
    ];

    // Relationships
    public function assignment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(VehicleAssignment::class, 'assignment_id');
    }

    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function vehicle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
