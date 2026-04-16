<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'last_login_ip',
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }
    
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Check if this user has a specific permission.
     * DB overrides take priority; otherwise use defaults.
     */
    public function hasPermission(string $permission): bool
    {
        $roleValue = $this->role instanceof UserRole ? $this->role->value : $this->role;

        // Check DB override first
        $override = \App\Models\RolePermission::where('role', $roleValue)
            ->where('permission', $permission)
            ->first();

        if ($override) {
            return $override->granted;
        }

        // Fall back to defaults
        return \App\Constants\PermissionDefaults::hasDefault($roleValue, $permission);
    }

    /**
     * Get all permissions for this user as a key => bool map.
     */
    public function allPermissions(): array
    {
        $roleValue = $this->role instanceof UserRole ? $this->role->value : $this->role;
        $keys = \App\Constants\PermissionDefaults::allKeys();
        $perms = [];

        // Load all DB overrides in one query
        $overrides = \App\Models\RolePermission::where('role', $roleValue)
            ->pluck('granted', 'permission')
            ->toArray();

        foreach ($keys as $key) {
            if (array_key_exists($key, $overrides)) {
                $perms[$key] = (bool) $overrides[$key];
            } else {
                $perms[$key] = \App\Constants\PermissionDefaults::hasDefault($roleValue, $key);
            }
        }

        return $perms;
    }
}
