<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class VehicleAssignment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'assigned_by',
        'status',
        'origin',
        'destination',
        'destination_latitude',
        'destination_longitude',
        'start_odometer',
        'end_odometer',
        'departure_time',
        'return_time',
        'notes',
        'vehicle_condition_on_return',
        'last_ping_at',         // Heartbeat: timestamp lokasi terakhir dari sopir
        'heartbeat_alerted_at', // Heartbeat: timestamp alert terakhir dikirim (cooldown)
    ];

    protected $casts = [
        'destination_latitude'  => 'float',
        'destination_longitude' => 'float',
        'start_odometer'        => 'decimal:2',
        'end_odometer'          => 'decimal:2',
        'departure_time'        => 'datetime',
        'return_time'           => 'datetime',
        'last_ping_at'          => 'datetime',
        'heartbeat_alerted_at'  => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'vehicle_id', 'driver_id', 'end_odometer', 'return_time'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function locations()
    {
        return $this->hasMany(DriverLocation::class, 'assignment_id');
    }

    public function latestLocation()
    {
        return $this->hasOne(DriverLocation::class, 'assignment_id')->latestOfMany('id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['assigned', 'confirmed', 'on_trip']);
    }

    public function scopeOnTrip($query)
    {
        return $query->where('status', 'on_trip');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helpers
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'assigned'  => 'Ditugaskan',
            'confirmed' => 'Dikonfirmasi Sopir',
            'on_trip'   => 'Dalam Perjalanan',
            'completed' => 'Selesai & Masuk Gudang',
            'cancelled' => 'Dibatalkan',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'assigned'  => 'blue',
            'confirmed' => 'teal',
            'on_trip'   => 'purple',
            'completed' => 'green',
            'cancelled' => 'gray',
            default     => 'gray',
        };
    }

    public function getDistanceTraveledAttribute(): ?float
    {
        if ($this->end_odometer && $this->start_odometer) {
            return max(0, (float) $this->end_odometer - (float) $this->start_odometer);
        }
        return null;
    }
}
