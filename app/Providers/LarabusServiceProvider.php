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
        $isBuiltin = $_ENV['DOMAIN_IS_BUILTIN'] ?? false;
        $builtinPath = $_ENV['DOMAIN_BUILTIN_PATH'] ?? null;

        // Настраиваем базы данных для приложения
        $this->configureDatabases($appName);

        if ($isBuiltin && $builtinPath) {
            // Load built-in app from core Larabus structure
            $this->loadBuiltinApp($builtinPath);
        } else {
            // Load regular app from apps folder
            $this->loadAppModels($appName);
            $this->loadAppViews($appName);
            $this->loadAppRoutes($appName);
        }
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

    private function loadBuiltinApp($builtinPath)
    {
        // Load built-in app from core Larabus structure (e.g., app/Admin)
        $basePath = base_path($builtinPath);
        
        // Load models
        $modelsPath = $basePath . '/Models';
        if (is_dir($modelsPath)) {
            $modelFiles = glob($modelsPath . '/*.php');
            foreach ($modelFiles as $modelFile) {
                require_once $modelFile;
                $className = pathinfo($modelFile, PATHINFO_FILENAME);
                error_log("LarabusServiceProvider: Loaded built-in model: {$className} from {$modelFile}");
            }
            error_log("LarabusServiceProvider: Loaded " . count($modelFiles) . " built-in models from {$modelsPath}");
        }
        
        // Load views - for built-in admin, we need to add the views to the main views directory
        $viewsPath = $basePath . '/resources/views';
        if (is_dir($viewsPath)) {
            // For built-in admin, copy views to the main resources/views directory
            $mainViewsPath = base_path('resources/views');
            $adminViewsPath = $mainViewsPath . '/admin';
            
            // Create admin views directory if it doesn't exist
            if (!is_dir($adminViewsPath)) {
                mkdir($adminViewsPath, 0755, true);
            }
            
            // Copy admin views to main views directory
            $this->copyDirectory($viewsPath, $adminViewsPath);
            error_log("LarabusServiceProvider: Copied built-in admin views to: {$adminViewsPath}");
        }
        
        // Load routes
        $routesPath = $basePath . '/routes.php';
        if (file_exists($routesPath)) {
            require $routesPath;
            error_log("LarabusServiceProvider: Loaded built-in routes from: {$routesPath}");
        }
        
        // Load services
        $servicesPath = $basePath . '/Services';
        if (is_dir($servicesPath)) {
            $serviceFiles = glob($servicesPath . '/*.php');
            foreach ($serviceFiles as $serviceFile) {
                require_once $serviceFile;
                $className = pathinfo($serviceFile, PATHINFO_FILENAME);
                error_log("LarabusServiceProvider: Loaded built-in service: {$className} from {$serviceFile}");
            }
        }
    }

    private function loadAppModels($appName)
    {
        $modelsPath = base_path("apps/{$appName}/Models");

        if (is_dir($modelsPath)) {
            // Manually include model files to make them available
            // This is a workaround when composer autoloader is not available
            $modelFiles = glob($modelsPath . '/*.php');
            
            foreach ($modelFiles as $modelFile) {
                // Include the model file to make the class available
                require_once $modelFile;
                $className = pathinfo($modelFile, PATHINFO_FILENAME);
                error_log("LarabusServiceProvider: Loaded model: {$className} from {$modelFile}");
            }
            
            error_log("LarabusServiceProvider: Loaded " . count($modelFiles) . " models from {$modelsPath}");
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
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $sourcePath = $source . '/' . $file;
                $destPath = $destination . '/' . $file;
                
                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $destPath);
                } else {
                    copy($sourcePath, $destPath);
                }
            }
        }
        closedir($dir);
    }
}
