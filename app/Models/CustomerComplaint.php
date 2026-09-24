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
        $prefix = "CMP-{$year}-";

        // Ambil nomor urut tertinggi tahun ini termasuk yang di-soft-delete
        $latest = static::withTrashed()
            ->where('complaint_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('complaint_number');

        $nextNumber = 1;
        if ($latest && preg_match('/CMP-\d{4}-(\d+)/', $latest, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }

        // Pastikan nomor benar-benar unik dan belum pernah dipakai di database
        while (static::withTrashed()->where('complaint_number', sprintf('CMP-%s-%04d', $year, $nextNumber))->exists()) {
            $nextNumber++;
        }

        return sprintf('CMP-%s-%04d', $year, $nextNumber);
    }
}
