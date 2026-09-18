<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'vehicle_code',
        'license_plate',
        'brand',
        'model',
        'vehicle_type',
        'year',
        'color',
        'chassis_number',
        'engine_number',
        'max_capacity_kg',
        'current_odometer_km',
        'fuel_type',
        'is_halal_dedicated',
        'status',
        'assigned_driver_id',
        'gps_device_id',
        'notes',
        'photo',
    ];

    protected $casts = [
        'is_halal_dedicated'  => 'boolean',
        'max_capacity_kg'     => 'decimal:2',
        'current_odometer_km' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['license_plate', 'status', 'assigned_driver_id', 'current_odometer_km'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function assignedDriver()
    {
        return $this->belongsTo(Employee::class, 'assigned_driver_id');
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'assigned_driver_id');
    }

    public function assignments()
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(VehicleAssignment::class)->whereIn('status', ['assigned', 'confirmed', 'on_trip'])->latestOfMany();
    }

    public function documents()
    {
        return $this->hasMany(VehicleDocument::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeHalal($query)
    {
        return $query->where('is_halal_dedicated', true);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active'      => 'green',
            'maintenance' => 'yellow',
            'inactive'    => 'gray',
            'scrapped'    => 'red',
            default       => 'gray',
        };
    }
}
