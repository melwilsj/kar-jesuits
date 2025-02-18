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

class User extends Authenticatable
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
        'phone',
        'password',
        'type',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
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
        if ($this->hasRole('superadmin')) return true;
        
        if ($this->isProvincial() && $member->jesuit?->province_id === $this->jesuit?->province_id) return true;
        
        if ($this->isSuperior() && $member->jesuit?->current_community_id === $this->jesuit?->current_community_id) return true;
        
        return false;
    }
}
