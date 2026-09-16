<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class VehicleDocument extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'document_type',
        'document_number',
        'title',
        'file_path',
        'issued_date',
        'expiry_date',
        'issuing_authority',
        'cost',
        'status',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'cost'        => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['document_type', 'status', 'expiry_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reminders()
    {
        return $this->morphMany(DocumentReminder::class, 'documentable');
    }

    // Scopes
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->where('status', 'active')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->whereDate('expiry_date', '>=', now());
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        return $this->expiry_date ? now()->diffInDays($this->expiry_date, false) : null;
    }

    public function getExpiryStatusColorAttribute(): string
    {
        if (!$this->expiry_date) return 'gray';
        $days = $this->days_until_expiry;
        if ($days < 0) return 'red';
        if ($days <= 7) return 'red';
        if ($days <= 14) return 'orange';
        if ($days <= 30) return 'yellow';
        return 'green';
    }
}
