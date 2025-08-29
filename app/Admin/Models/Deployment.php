<?php

namespace App\Admin\Models;

use App\Models\DomainModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Deployment Model
 * 
 * Tracks deployment history for apps and sites
 * Uses central SQLite database for management data
 */
class Deployment extends Model
{
    protected $table = 'deployments';
    
    protected $fillable = [
        'site_id',
        'app_name',
        'git_commit',
        'status',
        'deployed_by',
        'deployed_at',
        'error_message',
        'deployment_notes'
    ];
    
    protected $casts = [
        'deployed_at' => 'datetime'
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
     * Deployment belongs to a site
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
    
    /**
     * Deployment was done by a system user
     */
    public function deployedBy()
    {
        return $this->belongsTo(SystemUser::class, 'deployed_by');
    }
    
    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'success' => 'green',
            'failed' => 'red',
            'pending' => 'yellow',
            default => 'gray'
        };
    }
    
    /**
     * Get status icon
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'success' => '✅',
            'failed' => '❌',
            'pending' => '⏳',
            default => '❓'
        };
    }
    
    /**
     * Scope for successful deployments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }
    
    /**
     * Scope for failed deployments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
    
    /**
     * Scope for recent deployments
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('deployed_at', '>=', now()->subDays($days));
    }
}
