<?php

namespace App\Admin\Models;

use App\Models\DomainModel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * System User Model
 * 
 * For users who manage the Larabus system (different from app users)
 */
class SystemUser extends Authenticatable
{
    use Notifiable;
    
    protected $table = 'system_users';
    
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'is_active'
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    /**
     * Get the database connection for this model
     * System users are stored in the central SQLite database
     */
    public function getConnectionName()
    {
        // Always use the central SQLite database for management data
        // This keeps management data with framework data (sessions, cache, etc.)
        return 'sqlite'; // Laravel's default connection
    }
    
    /**
     * System user has many deployments
     */
    public function deployments()
    {
        return $this->hasMany(Deployment::class, 'deployed_by');
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if user is manager
     */
    public function isManager()
    {
        return in_array($this->role, ['admin', 'manager']);
    }
    
    /**
     * Check if user can deploy
     */
    public function canDeploy()
    {
        return in_array($this->role, ['admin', 'manager', 'developer']);
    }
    
    /**
     * Get role badge color
     */
    public function getRoleColorAttribute()
    {
        return match($this->role) {
            'admin' => 'red',
            'manager' => 'blue',
            'developer' => 'green',
            default => 'gray'
        };
    }
    
    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
