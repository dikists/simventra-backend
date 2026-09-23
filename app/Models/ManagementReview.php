<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ManagementReview extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'review_date',
        'review_type',
        'agenda',
        'minutes',
        'conclusions',
        'status',
        'created_by',
    ];

    protected $casts = [
        'review_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'review_date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actionItems()
    {
        return $this->hasMany(ReviewActionItem::class, 'review_id');
    }
}
