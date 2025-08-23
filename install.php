<?php

/**
 * Larabus Installation Script
 * 
 * This script helps set up Larabus framework on hosting
 */

echo "🚍 Larabus Framework Installation\n";
echo "================================\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    die("❌ Error: PHP 8.2+ is required. Current version: " . PHP_VERSION . "\n");
}

echo "✅ PHP version check passed: " . PHP_VERSION . "\n";

// Check if composer.json exists
if (!file_exists('composer.json')) {
    die("❌ Error: composer.json not found. Are you in the Larabus directory?\n");
}

echo "✅ Composer configuration found\n";

// Check if vendor directory exists
if (!is_dir('vendor')) {
    echo "📦 Installing Composer dependencies...\n";
    system('composer install --no-dev --optimize-autoloader');
    echo "✅ Dependencies installed\n";
}

// Setup .env file
if (!file_exists('.env')) {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "✅ .env file created from example\n";
    } else {
        die("❌ Error: .env.example not found\n");
    }
}

// Generate APP_KEY if not set
$envContent = file_get_contents('.env');
if (strpos($envContent, 'APP_KEY=') !== false && strpos($envContent, 'APP_KEY=') === strpos($envContent, 'APP_KEY=')) {
    echo "🔑 Generating application key...\n";
    system('php artisan key:generate');
    echo "✅ Application key generated\n";
}

// Create database if SQLite
if (strpos($envContent, 'DB_CONNECTION=sqlite') !== false) {
    $dbPath = 'database/database.sqlite';
    if (!file_exists($dbPath)) {
        touch($dbPath);
        echo "✅ SQLite database created\n";
    }
    
    echo "🗄️ Running migrations...\n";
    system('php artisan migrate --force');
    echo "✅ Database migrated\n";
}

// Set permissions
if (is_dir('storage')) {
    chmod('storage', 0755);
    system('chmod -R 755 storage');
    echo "✅ Storage permissions set\n";
}

if (is_dir('bootstrap/cache')) {
    chmod('bootstrap/cache', 0755);
    system('chmod -R 755 bootstrap/cache');
    echo "✅ Bootstrap cache permissions set\n";
}

echo "\n🎉 Larabus installation completed!\n\n";

echo "📋 Next steps:\n";
echo "1. Create your domain folders (site1/, site2/, etc.)\n";
echo "2. Add your apps in apps/ directory\n";
echo "3. Configure your web server virtual hosts\n";
echo "4. Point domains to their respective folders\n\n";

echo "📖 Documentation: https://github.com/Kalmanis/Larabus\n";
echo "🚀 Happy coding with Larabus!\n";
