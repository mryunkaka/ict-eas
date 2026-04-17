<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'unit_id',
    'employee_id',
    'name',
    'email',
    'password',
    'role',
    'job_title',
    'phone',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function ictRequests(): HasMany
    {
        return $this->hasMany(IctRequest::class, 'requester_id');
    }

    public function emailRequests(): HasMany
    {
        return $this->hasMany(EmailRequest::class, 'requester_id');
    }

    public function repairRequests(): HasMany
    {
        return $this->hasMany(RepairRequest::class, 'requester_id');
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class, 'reported_by_id');
    }

    public function projectRequests(): HasMany
    {
        return $this->hasMany(ProjectRequest::class, 'requester_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_user_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isIctAdmin(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::AdminIct], true);
    }

    public function isUnitUser(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::UnitUser], true);
    }

    public function isStaffIct(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::StaffIct], true);
    }

    public function isAsmenIct(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::AsmenIct], true);
    }

    public function isManagerIct(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::ManagerIct], true);
    }

    public function canCreateIctRequest(): bool
    {
        return $this->isUnitUser() || $this->isIctAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canPermanentDeleteIctRequest(): bool
    {
        return $this->isAsmenIct() || $this->isManagerIct();
    }

    public function canProcessApprovals(): bool
    {
        return $this->isIctAdmin() || $this->isStaffIct() || $this->isAsmenIct() || $this->isManagerIct();
    }

    public function canAccessAssetHandovers(): bool
    {
        // Asmen dan di atasnya: full akses lintas unit.
        // Admin ICT juga tetap boleh akses sesuai kebutuhan operasional.
        return $this->isAsmenIct() || $this->isManagerIct() || $this->isIctAdmin();
    }
}
