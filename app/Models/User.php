<?php

namespace App\Models;

use App\Traits\{
    HasProvinceAccess,
    HasRegionAccess,
    HasCommunityAccess,
    HasCommissionAccess,
    HasInstitutionAccess,
    HasRolesAndPermissions
};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends AuthenticatableModel
{
    use HasApiTokens,
        HasRolesAndPermissions,
        HasProvinceAccess,
        HasRegionAccess,
        HasCommunityAccess,
        HasCommissionAccess,
        HasInstitutionAccess,
        HasFactory,
        SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'is_active',
        'phone_number',
        'firebase_uid',
        'auth_provider'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'firebase_uid',
        'google_id'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'type' => 'string'
    ];

    // Relationships
    public function jesuit()
    {
        return $this->hasOne(Jesuit::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // Helper methods
    public function isProvincial(): bool
    {
        return $this->jesuit?->activeRoles()
            ->whereHasMorph('assignable', [Province::class])
            ->where('role_type_id', RoleType::where('name', 'Provincial')->first()->id)
            ->exists() ?? false;
    }

    public function isSuperior(): bool
    {
        return $this->jesuit?->activeRoles()
            ->whereHasMorph('assignable', [Community::class])
            ->whereIn('role_type_id', RoleType::whereIn('name', ['Superior', 'Rector'])->pluck('id'))
            ->exists() ?? false;
    }

    public function canViewMemberDetails(User $member): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }

        if ($this->isPOSA() && $member->jesuit?->currentCommunity?->isCommonHouse()) {
            return true;
        }

        if ($member->jesuit?->currentCommunity?->isCommonHouse()) {
            return false;
        }

        return $this->canAccessProvince($member->jesuit?->province);
    }

    public function isPOSA(): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }

        return $this->jesuit?->activeRoles()
            ->whereHasMorph('assignable', [Assistancy::class])
            ->where('role_type_id', RoleType::where('name', 'POSA')->first()->id)
            ->exists() ?? false;
    }

    // Add new method for Firebase auth
    public function isFirebaseUser(): bool
    {
        return $this->auth_provider === 'firebase' || $this->auth_provider === 'google';
    }
}
