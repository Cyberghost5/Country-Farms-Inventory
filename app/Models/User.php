<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'role', 'company_name', 'state', 'address', 'is_active', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ── Role constants ────────────────────────────────────────────
    const ROLE_SUPER_ADMIN        = 'super_admin';
    const ROLE_GENERAL_MANAGER    = 'general_manager';
    const ROLE_PRODUCTION_MANAGER = 'production_manager';
    const ROLE_STORE_MANAGER      = 'store_manager';
    const ROLE_DISTRIBUTOR        = 'distributor';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Role helpers ──────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isGeneralManager(): bool
    {
        return $this->role === self::ROLE_GENERAL_MANAGER;
    }

    public function isProductionManager(): bool
    {
        return $this->role === self::ROLE_PRODUCTION_MANAGER;
    }

    public function isStoreManager(): bool
    {
        return $this->role === self::ROLE_STORE_MANAGER;
    }

    public function isDistributor(): bool
    {
        return $this->role === self::ROLE_DISTRIBUTOR;
    }

    /** Super admin OR general manager (oversight roles) */
    public function isOversight(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_GENERAL_MANAGER], true);
    }

    /** Super admin OR general manager OR store manager */
    public function canViewInventory(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_GENERAL_MANAGER,
            self::ROLE_PRODUCTION_MANAGER,
            self::ROLE_STORE_MANAGER,
        ], true);
    }

    /** Roles that can dispatch products */
    public function canDispatch(): bool
    {
        return $this->role === self::ROLE_STORE_MANAGER;
    }

    /** Roles that can upload inventory batches */
    public function canUploadInventory(): bool
    {
        return $this->role === self::ROLE_PRODUCTION_MANAGER;
    }

    /** Roles that can view financial / pricing data */
    public function canViewFinancials(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_DISTRIBUTOR], true);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'distributor_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'distributor_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            self::ROLE_SUPER_ADMIN        => 'Super Admin',
            self::ROLE_GENERAL_MANAGER    => 'General Manager',
            self::ROLE_PRODUCTION_MANAGER => 'Production Manager',
            self::ROLE_STORE_MANAGER      => 'Store Manager',
            self::ROLE_DISTRIBUTOR        => 'Distributor',
            default                       => ucfirst($this->role),
        };
    }
}
