<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Billable, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    /**
     * Dynamically resolve the database connection name for the User model.
     * This forces central users to validate against the landlord DB, even inside a tenant request.
     */
    public function getConnectionName(): ?string
    {
        // If we are performing a central owner operation, force the central database connection
        if (request()->offsetGet('is_central_auth_context') === true) {
            return 'mysql';
        }

        return parent::getConnectionName();
    }

    /**
     * Determine if the user is the absolute owner of the current tenant space.
     */
    public function isTenantOwner(): bool
    {
        if (! function_exists('tenant') || ! tenant()) {
            return false;
        }

        return (string) $this->id === (string) tenant('owner_id');
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps();
    }

    /**
     * Check if the user has a specific role or one of the specified roles.
     */
    public function hasRole(string|array $role): bool
    {
        // Absolute tenant owners bypass all standard role-based checks
        if ($this->isTenantOwner()) {
            return true;
        }

        if (is_array($role)) {
            return $this->roles->pluck('name')->intersect($role)->isNotEmpty();
        }

        return $this->roles->contains('name', $role);
    }

    /**
     * Safely assign a tenant role to the user by its string name.
     */
    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $this->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    /**
     * Remove a tenant role from the user by its string name.
     */
    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $this->roles()->detach($role->id);
        }
    }
}
