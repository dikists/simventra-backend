<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CustomerComplaint extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'complaint_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'subject',
        'description',
        'received_at',
        'vehicle_id',
        'driver_id',
        'assigned_pic',
        'status',
        'priority',
        'resolution',
        'resolved_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['complaint_number', 'status', 'priority', 'assigned_pic'])
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

    public function pic()
    {
        return $this->belongsTo(User::class, 'assigned_pic');
    }

    public function followups()
    {
        return $this->hasMany(ComplaintFollowup::class, 'complaint_id')->latest();
    }

    public static function generateComplaintNumber(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('CMP-%s-%04d', $year, $count);
    }
}
