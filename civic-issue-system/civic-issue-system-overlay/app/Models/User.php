<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'department_id', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isCitizen(): bool { return $this->role === 'citizen'; }
    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isStaff(): bool   { return $this->role === 'staff'; }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function assignedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    /** Role-aware link to a complaint page (used by notifications). */
    public function complaintUrl(int $complaintId): string
    {
        return match ($this->role) {
            'admin' => route('admin.complaints.show', $complaintId),
            'staff' => route('staff.complaints.show', $complaintId),
            default => route('citizen.complaints.show', $complaintId),
        };
    }
}
