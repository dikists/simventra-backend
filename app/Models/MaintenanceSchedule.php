<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MaintenanceSchedule extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'trigger_type',
        'trigger_km',
        'trigger_days',
        'last_done_km',
        'last_done_date',
        'next_due_km',
        'next_due_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'trigger_km' => 'decimal:2',
        'trigger_days' => 'integer',
        'last_done_km' => 'decimal:2',
        'last_done_date' => 'date',
        'next_due_km' => 'decimal:2',
        'next_due_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['vehicle_id', 'maintenance_type', 'is_active', 'next_due_km', 'next_due_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class, 'schedule_id');
    }

    public function isDue(float $currentOdoKm = 0): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->next_due_km && $currentOdoKm >= $this->next_due_km) {
            return true;
        }

        if ($this->next_due_date && now()->gte($this->next_due_date)) {
            return true;
        }

        return false;
    }
}
