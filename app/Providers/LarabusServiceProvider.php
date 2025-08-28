<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class LarabusServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Регистрируем сервисы Larabus
    }

    public function boot()
    {
        // Загружаем конфигурацию из переменных окружения (установленных в index.php)
        $appName = $_ENV['DOMAIN_APP_NAME'] ?? 'site1';

        // Настраиваем базы данных для приложения
        $this->configureDatabases($appName);

        // Добавляем пути к views из apps
        $this->loadAppViews($appName);

        // Загружаем маршруты из apps
        $this->loadAppRoutes($appName);
    }

    private function loadAppRoutes($appName)
    {
        $routesPath = base_path("apps/{$appName}/routes.php");

        if (file_exists($routesPath)) {
            require $routesPath;
        }
    }

    private function loadAppViews($appName)
    {
        $viewsPath = base_path("apps/{$appName}/resources/views");

        if (is_dir($viewsPath)) {
            View::addLocation($viewsPath);
        }
    }

    private function configureDatabases($appName)
    {
        // Get database configurations from environment variables set by domain config
        $databases = $this->getDatabaseConnections();
        
        // Debug logging
        error_log("LarabusServiceProvider: configureDatabases called for app: " . $appName);
        error_log("LarabusServiceProvider: Found " . count($databases) . " database connections");
        
        if (!empty($databases)) {
            error_log("LarabusServiceProvider: Database connections found: " . json_encode(array_keys($databases)));
            
            // Merge app-specific database connections with existing config
            // This ADDS new connections but KEEPS the central Laravel default (sqlite)
            $existingConnections = Config::get('database.connections', []);
            $mergedConnections = array_merge($existingConnections, $databases);
            
            // Set the merged connections
            Config::set('database.connections', $mergedConnections);
            
            // Store domain default connection for models to use
            $domainDefaultConnection = $_ENV['DOMAIN_DB_DOMAIN_DEFAULT'] ?? null;
            error_log("LarabusServiceProvider: Domain default connection: " . ($domainDefaultConnection ?? 'null'));
            
            if ($domainDefaultConnection && isset($databases[$domainDefaultConnection])) {
                // Store the domain default for app models to use
                Config::set('larabus.domain_default_connection', $domainDefaultConnection);
                error_log("LarabusServiceProvider: Set domain default connection to: " . $domainDefaultConnection);
                
                // DON'T change Laravel's default - keep it as sqlite for framework operations
                error_log("LarabusServiceProvider: Keeping Laravel default as sqlite for framework operations");
            } else {
                error_log("LarabusServiceProvider: Domain default connection not valid");
            }
        } else {
            error_log("LarabusServiceProvider: No database connections found in environment variables");
        }
    }

    private function getDatabaseConnections()
    {
        $connections = [];
        
        // Look for database configurations in environment variables
        // Format: DOMAIN_DB_CONNECTIONS_[CONNECTION_NAME]_[PARAM]
        
        foreach ($_ENV as $key => $value) {
            if (strpos($key, 'DOMAIN_DB_CONNECTIONS_') === 0) {
                // Extract connection name and parameter
                $remainder = str_replace('DOMAIN_DB_CONNECTIONS_', '', $key);
                $parts = explode('_', $remainder);
                
                if (count($parts) >= 3) {
                    // For TESTSITE_MYSQL_DRIVER, we want "testsite_mysql" as connection name
                    $connectionName = strtolower($parts[0] . '_' . $parts[1]);
                    $paramName = strtolower(implode('_', array_slice($parts, 2)));
                    
                    if (!isset($connections[$connectionName])) {
                        $connections[$connectionName] = [];
                    }
                    
                    $connections[$connectionName][$paramName] = $value;
                    
                    error_log("LarabusServiceProvider: Parsed connection '{$connectionName}' param '{$paramName}' = '{$value}'");
                }
            }
        }
        
        error_log("LarabusServiceProvider: Final connections: " . json_encode(array_keys($connections)));
        return $connections;
    }
}
