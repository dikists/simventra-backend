<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'employee_number',
        'name',
        'type',
        'employment_status',
        'position',
        'department',
        'phone',
        'email',
        'address',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'nik',
        'npwp',
        'join_date',
        'contract_end_date',
        'sim_number',
        'sim_type',
        'sim_expiry',
        'license_plate_assigned',
        'has_skck',
        'skck_expiry',
        'criminal_record_notes',
        'id_card_number',
        'id_card_expiry',
        'id_card_active',
        'status',
        'notes',
        'photo',
    ];

    protected $casts = [
        'date_of_birth'    => 'date',
        'join_date'        => 'date',
        'contract_end_date'=> 'date',
        'sim_expiry'       => 'date',
        'skck_expiry'      => 'date',
        'id_card_expiry'   => 'date',
        'has_skck'         => 'boolean',
        'id_card_active'   => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'employment_status', 'status', 'position', 'sim_expiry'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatives()
    {
        return $this->hasMany(EmployeeRelative::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'assigned_driver_id');
    }

    public function assignments()
    {
        return $this->hasMany(VehicleAssignment::class, 'driver_id');
    }

    public function activeAssignment()
    {
        return $this->hasOne(VehicleAssignment::class, 'driver_id')->whereIn('status', ['assigned', 'confirmed', 'on_trip'])->latestOfMany();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDrivers($query)
    {
        return $query->where('type', 'sopir');
    }

    public function scopeExpiringSimIn($query, int $days)
    {
        return $query->whereNotNull('sim_expiry')
            ->whereDate('sim_expiry', '<=', now()->addDays($days))
            ->whereDate('sim_expiry', '>=', now());
    }

    // Accessors
    public function getFullTypeAttribute(): string
    {
        return match ($this->type) {
            'karyawan' => 'Karyawan',
            'sopir'    => 'Sopir',
            'mitra'    => 'Mitra',
            default    => ucfirst($this->type),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active'     => 'green',
            'inactive'   => 'yellow',
            'terminated' => 'red',
            default      => 'gray',
        };
    }
}
