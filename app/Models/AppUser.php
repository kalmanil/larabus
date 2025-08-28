<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;

/**
 * App-specific User model
 * 
 * This model automatically uses the domain's default connection
 * for app-specific user data, separate from Laravel's central User model.
 */
class AppUser extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'app_users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'app_name', // Track which app this user belongs to
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the database connection for this model
     * Uses the domain's default connection
     */
    public function getConnectionName()
    {
        // Use the domain's default connection if available
        $domainConnection = Config::get('larabus.domain_default_connection');
        
        if ($domainConnection) {
            return $domainConnection;
        }
        
        // Fallback to Laravel's default (central sqlite)
        return parent::getConnectionName();
    }
}
