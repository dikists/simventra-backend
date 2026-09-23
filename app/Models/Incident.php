<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Incident extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'incident_number',
        'type',
        'severity',
        'title',
        'description',
        'occurred_at',
        'location',
        'latitude',
        'longitude',
        'vehicle_id',
        'driver_id',
        'reported_by',
        'assignment_id',
        'status',
        'resolution',
        'resolved_at',
        'photo',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['incident_number', 'type', 'severity', 'status'])
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

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignment()
    {
        return $this->belongsTo(VehicleAssignment::class, 'assignment_id');
    }

    public function followups()
    {
        return $this->hasMany(IncidentFollowup::class)->latest();
    }

    public static function generateIncidentNumber(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('INC-%s-%04d', $year, $count);
    }
}
