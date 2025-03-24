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
use Illuminate\Notifications\Notifiable;

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
        SoftDeletes,
        Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'is_active',
        'phone_number',
        'firebase_uid',
        'auth_provider',
        'fcm_tokens'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'firebase_uid',
        'google_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'type' => 'string',
        'fcm_tokens' => 'array',
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

    public function isProvinceAdmin(): bool
    {
        return $this->type === 'admin' && $this->jesuit?->province_id && !$this->jesuit?->region_id;
    }

    public function isRegionAdmin(): bool
    {
        return $this->type === 'admin' && $this->jesuit?->region_id;
    }

    public function isAdmin(): bool
    {
        return $this->isProvinceAdmin() || $this->isRegionAdmin() || $this->isSuperAdmin();
    }

    public function isSuperAdmin(): bool
    {
        return $this->type === 'superadmin';
    }

    /**
     * Add a new FCM token for this user
     */
    public function addFcmToken(string $token): void
    {
        $tokens = $this->fcm_tokens ?? [];
        
        if (!in_array($token, $tokens)) {
            $tokens[] = $token;
            $this->fcm_tokens = $tokens;
            $this->save();
        }
    }

    /**
     * Remove an FCM token for this user
     */
    public function removeFcmToken(string $token): void
    {
        $tokens = $this->fcm_tokens ?? [];
        
        if (in_array($token, $tokens)) {
            $tokens = array_filter($tokens, fn($t) => $t !== $token);
            $this->fcm_tokens = array_values($tokens); // Reindex array
            $this->save();
        }
    }
}
