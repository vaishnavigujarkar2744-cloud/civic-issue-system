<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Complaint extends Model
{
    public const CATEGORIES = [
        'Roads & Potholes', 'Garbage & Sanitation', 'Water Supply',
        'Street Lights', 'Drainage & Sewage', 'Public Property', 'Other',
    ];

    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
    ];

    public const STATUS_COLORS = [
        'pending' => 'secondary',
        'assigned' => 'info',
        'in_progress' => 'warning',
        'resolved' => 'success',
        'rejected' => 'danger',
    ];

    protected $fillable = [
        'user_id', 'department_id', 'assigned_to', 'title', 'description', 'category',
        'address', 'latitude', 'longitude', 'image', 'status', 'priority', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    protected $appends = ['status_label', 'image_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ComplaintUpdate::class)->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}
