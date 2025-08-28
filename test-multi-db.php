<?php

/**
 * Multi-Database Test Script for Larabus
 * 
 * This script demonstrates how each app can have its own database connections
 */

require __DIR__ . '/vendor/autoload.php';

echo "🚍 Larabus Multi-Database Test\n";
echo "==============================\n\n";

// Simulate different domain configurations
$testConfigs = [
    'site1' => [
        'app_name' => 'site1',
        'site_title' => 'Site 1 - Blue Theme',
        'view_template' => 'site1.welcome',
        'theme_color' => '#3b82f6',
        'db_default' => 'site1_sqlite',
        'db_connections' => [
            'site1_sqlite' => [
                'driver' => 'sqlite',
                'database' => __DIR__ . '/database/site1.sqlite',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]
    ],
    'site2' => [
        'app_name' => 'site2',
        'site_title' => 'Site 2 - Green Theme',
        'view_template' => 'site2.welcome',
        'theme_color' => '#10b981',
        'db_default' => 'site2_sqlite',
        'db_connections' => [
            'site2_sqlite' => [
                'driver' => 'sqlite',
                'database' => __DIR__ . '/database/site2.sqlite',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]
    ],
    'testsite' => [
        'app_name' => 'testsite',
        'site_title' => 'Test Soy Crucerista',
        'view_template' => 'testsite.welcome',
        'theme_color' => '#4f46e5',
        'db_default' => 'testsite_sqlite',
        'db_connections' => [
            'testsite_sqlite' => [
                'driver' => 'sqlite',
                'database' => __DIR__ . '/database/testsite.sqlite',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]
    ]
];

foreach ($testConfigs as $appName => $config) {
    echo "Testing app: {$appName}\n";
    echo "-------------------\n";
    
    // Clear environment
    foreach ($_ENV as $key => $value) {
        if (strpos($key, 'DOMAIN_') === 0) {
            unset($_ENV[$key]);
        }
    }
    
    // Set environment variables like domain index.php would
    foreach ($config as $key => $value) {
        if ($key === 'db_connections' && is_array($value)) {
            foreach ($value as $connectionName => $connectionConfig) {
                foreach ($connectionConfig as $paramName => $paramValue) {
                    $_ENV['DOMAIN_DB_CONNECTIONS_' . strtoupper($connectionName) . '_' . strtoupper($paramName)] = $paramValue;
                }
            }
        } else {
            $_ENV['DOMAIN_' . strtoupper($key)] = $value;
        }
    }
    
    // Create SQLite database file if it doesn't exist
    $dbPath = $config['db_connections'][array_key_first($config['db_connections'])]['database'];
    if (!file_exists($dbPath)) {
        touch($dbPath);
        echo "✅ Created database: {$dbPath}\n";
    }
    
    // Test database configuration extraction
    $connections = [];
    foreach ($_ENV as $key => $value) {
        if (strpos($key, 'DOMAIN_DB_CONNECTIONS_') === 0) {
            $parts = explode('_', str_replace('DOMAIN_DB_CONNECTIONS_', '', $key));
            if (count($parts) >= 2) {
                $connectionName = strtolower($parts[0]);
                $paramName = strtolower(implode('_', array_slice($parts, 1)));
                
                if (!isset($connections[$connectionName])) {
                    $connections[$connectionName] = [];
                }
                
                $connections[$connectionName][$paramName] = $value;
            }
        }
    }
    
    echo "📊 Database connections extracted:\n";
    foreach ($connections as $connName => $connConfig) {
        $driver = $connConfig['driver'] ?? 'unknown';
        $database = $connConfig['database'] ?? 'unknown';
        echo "  - {$connName}: {$driver} -> {$database}\n";
    }
    
    echo "🎯 Default connection: {$_ENV['DOMAIN_DB_DEFAULT']}\n";
    echo "\n";
}

echo "✅ Multi-database configuration test completed!\n\n";

echo "📋 How to use in your models:\n";
echo "------------------------------\n";
echo "// Use default connection (automatically set per app)\n";
echo "User::all(); // Uses app-specific default connection\n\n";
echo "// Use specific connection\n";
echo "User::on('site1_sqlite')->all(); // Force specific connection\n\n";
echo "// In migration\n";
echo "Schema::connection('site2_mysql')->create('users', function...);\n\n";

echo "🚀 Each app now has its own isolated database!\n";
