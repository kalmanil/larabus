<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/**
 * Site Management Model
 * 
 * Manages sites/domains in the Larabus system
 * Uses central SQLite database for management data
 */
class Site extends Model
{
    protected $table = 'managed_sites';
    
    protected $fillable = [
        'domain',
        'app_name',
        'site_title',
        'theme_color',
        'status',
        'app_repository',
        'app_branch',
        'auto_deploy',
        'notes'
    ];
    
    protected $casts = [
        'auto_deploy' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the database connection for this model
     * Management data is stored in the central SQLite database
     */
    public function getConnectionName()
    {
        // Always use the central SQLite database for management data
        return 'sqlite'; // Laravel's default connection
    }
    
    /**
     * Site has many deployments
     */
    public function deployments()
    {
        return $this->hasMany(Deployment::class);
    }
    
    /**
     * Get latest deployment
     */
    public function latestDeployment()
    {
        return $this->hasOne(Deployment::class)->latest('deployed_at');
    }
    
    /**
     * Get path to domain config file
     */
    public function getConfigPath()
    {
        return "../{$this->domain}/config.php";
    }
    
    /**
     * Get path to domain folder
     */
    public function getDomainPath()
    {
        return "../{$this->domain}";
    }
    
    /**
     * Get path to app folder
     */
    public function getAppPath()
    {
        return base_path("apps/{$this->app_name}");
    }
    
    /**
     * Check if domain folder exists
     */
    public function domainExists()
    {
        return is_dir($this->getDomainPath());
    }
    
    /**
     * Check if app folder exists
     */
    public function appExists()
    {
        return is_dir($this->getAppPath());
    }
    
    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'red',
            'maintenance' => 'yellow',
            default => 'gray'
        };
    }
    
    /**
     * Get status icon
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'active' => '✅',
            'inactive' => '❌',
            'maintenance' => '🔧',
            default => '❓'
        };
    }
    
    /**
     * Scope for active sites
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Scope for inactive sites
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
    
    /**
     * Scope for maintenance sites
     */
    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }
}
