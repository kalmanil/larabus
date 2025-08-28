<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * Base model for domain-specific data
 * 
 * App models should extend this to automatically use the domain's default connection
 * while keeping Laravel framework data in the central SQLite database.
 */
abstract class DomainModel extends Model
{
    /**
     * Get the database connection for this model
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
