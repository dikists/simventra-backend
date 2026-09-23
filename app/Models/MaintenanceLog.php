<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MaintenanceLog extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'schedule_id',
        'maintenance_date',
        'maintenance_type',
        'description',
        'workshop',
        'mechanic',
        'cost',
        'odometer_km',
        'status',
        'parts_replaced',
        'receipt_photo',
        'reported_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'cost' => 'decimal:2',
        'odometer_km' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['vehicle_id', 'maintenance_type', 'cost', 'odometer_km', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function schedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'schedule_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
