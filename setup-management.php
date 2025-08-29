<?php

/**
 * Larabus Management Setup Script
 * 
 * Sets up the central management system for Larabus
 */

echo "🚍 Larabus Management Setup\n";
echo "===========================\n\n";

// Check if we're in the right directory
if (!file_exists('composer.json') || !is_dir('apps')) {
    echo "❌ Error: Please run this script from the Larabus root directory.\n";
    exit(1);
}

// 1. Create admin app if it doesn't exist
echo "📂 Setting up admin app...\n";

$adminAppPath = 'apps/admin';
if (!is_dir($adminAppPath)) {
    mkdir($adminAppPath, 0755, true);
    mkdir("{$adminAppPath}/Models", 0755, true);
    mkdir("{$adminAppPath}/Services", 0755, true);
    mkdir("{$adminAppPath}/database", 0755, true);
    mkdir("{$adminAppPath}/database/migrations", 0755, true);
    mkdir("{$adminAppPath}/resources", 0755, true);
    mkdir("{$adminAppPath}/resources/views", 0755, true);
    mkdir("{$adminAppPath}/resources/views/admin", 0755, true);
    echo "✅ Created admin app structure\n";
} else {
    echo "ℹ️ Admin app already exists\n";
}

// 2. Create admin domain if it doesn't exist
echo "🌐 Setting up admin domain...\n";

$adminDomain = 'admin.larabus.dev';
$domainPath = "../{$adminDomain}";

if (!is_dir($domainPath)) {
    mkdir($domainPath, 0755, true);
    
    // Create config.php for admin domain
    $adminConfig = '<?php

return [
    \'app_name\' => \'admin\',
    \'site_title\' => \'Larabus Management Dashboard\',
    \'view_template\' => \'admin.dashboard\',
    \'theme_color\' => \'#1f2937\',
    
    // Management database configuration
    \'db_domain_default\' => \'management_mysql\',
    \'db_connections\' => [
        \'management_mysql\' => [
            \'driver\' => \'mysql\',
            \'host\' => \'127.0.0.1\',
            \'port\' => \'3306\',
            \'database\' => \'larabus_management\',
            \'username\' => \'root\',
            \'password\' => \'\',
            \'charset\' => \'utf8mb4\',
            \'collation\' => \'utf8mb4_unicode_ci\',
            \'prefix\' => \'\',
            \'prefix_indexes\' => true,
            \'strict\' => true,
            \'engine\' => null,
        ],
        \'management_sqlite\' => [
            \'driver\' => \'sqlite\',
            \'database\' => __DIR__ . \'/../larabus/database/management.sqlite\',
            \'prefix\' => \'\',
            \'foreign_key_constraints\' => true,
        ],
    ]
];';
    
    file_put_contents("{$domainPath}/config.php", $adminConfig);
    
    // Copy index.php template
    if (file_exists('templates/domain-index.php.template')) {
        copy('templates/domain-index.php.template', "{$domainPath}/index.php");
    }
    
    // Copy .htaccess template
    if (file_exists('templates/domain-htaccess.template')) {
        copy('templates/domain-htaccess.template', "{$domainPath}/.htaccess");
    }
    
    // Create router.php for development
    $routerContent = '<?php

// Router script for PHP built-in server
$uri = urldecode(parse_url($_SERVER[\'REQUEST_URI\'], PHP_URL_PATH));

if ($uri !== \'/\' && file_exists(__DIR__ . $uri)) {
    return false;
}

require_once __DIR__ . \'/index.php\';';
    
    file_put_contents("{$domainPath}/router.php", $routerContent);
    
    echo "✅ Created admin domain: {$adminDomain}\n";
} else {
    echo "ℹ️ Admin domain already exists\n";
}

// 3. Create management database
echo "🗄️ Setting up management database...\n";

$dbChoice = readline("Choose database for management data:\n1) MySQL (recommended)\n2) SQLite (simpler setup)\nEnter choice (1 or 2): ");

if ($dbChoice === '1') {
    // MySQL setup
    $dbName = readline("Database name [larabus_management]: ") ?: 'larabus_management';
    $dbUser = readline("Database user [root]: ") ?: 'root';
    $dbPass = readline("Database password []: ");
    $dbHost = readline("Database host [127.0.0.1]: ") ?: '127.0.0.1';
    
    // Test connection
    try {
        $pdo = new PDO("mysql:host={$dbHost}", $dbUser, $dbPass);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
        echo "✅ Management database '{$dbName}' created/verified\n";
    } catch (PDOException $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
        echo "Please create the database manually and run the migrations.\n";
    }
    
} else {
    // SQLite setup
    $sqliteFile = 'database/management.sqlite';
    if (!file_exists($sqliteFile)) {
        touch($sqliteFile);
        echo "✅ Created SQLite database: {$sqliteFile}\n";
    } else {
        echo "ℹ️ SQLite database already exists\n";
    }
}

// 4. Create basic admin view
echo "🎨 Creating basic admin views...\n";

$dashboardView = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteName ?? "Larabus Management" }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-gray-800 text-white p-4">
            <div class="container mx-auto">
                <h1 class="text-2xl font-bold">🚍 Larabus Management</h1>
                <p class="text-gray-300">Central control panel for all your sites</p>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="container mx-auto p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Stats Cards -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-700">Total Sites</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats[\'total_sites\'] ?? 0 }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-700">Active Sites</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $stats[\'active_sites\'] ?? 0 }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-700">Recent Deployments</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats[\'recent_deployments\'] ?? 0 }}</p>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-xl font-bold mb-4">Quick Actions</h2>
                <div class="space-x-4">
                    <a href="/sites/create" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        ➕ Create New Site
                    </a>
                    <a href="/sites" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        🌐 Manage Sites
                    </a>
                    <a href="/api/stats" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        📊 View API Stats
                    </a>
                </div>
            </div>
            
            <!-- Sites Overview -->
            @if(isset($sites) && $sites->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Sites Overview</h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Domain</th>
                                <th class="px-4 py-2 text-left">App</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Last Deployment</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sites as $site)
                            <tr class="border-t">
                                <td class="px-4 py-2">
                                    <a href="/sites/{{ $site->id }}" class="text-blue-600 hover:underline">
                                        {{ $site->domain }}
                                    </a>
                                </td>
                                <td class="px-4 py-2">{{ $site->app_name }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 rounded text-sm 
                                        @if($site->status === \'active\') bg-green-100 text-green-800
                                        @elseif($site->status === \'maintenance\') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($site->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    {{ $site->latestDeployment?->deployed_at?->diffForHumans() ?? \'Never\' }}
                                </td>
                                <td class="px-4 py-2">
                                    @if($site->app_repository)
                                    <button onclick="deploySite({{ $site->id }})" 
                                            class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                        🚀 Deploy
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <h2 class="text-xl font-bold mb-4">No Sites Yet</h2>
                <p class="text-gray-600 mb-4">Get started by creating your first site!</p>
                <a href="/sites/create" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
                    ➕ Create Your First Site
                </a>
            </div>
            @endif
        </main>
    </div>
    
    <script>
        function deploySite(siteId) {
            if (confirm(\'Deploy this site?\')) {
                fetch(`/deploy/site/${siteId}`, { method: \'POST\' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(\'Deployment started successfully!\');
                            location.reload();
                        } else {
                            alert(\'Deployment failed: \' + data.message);
                        }
                    })
                    .catch(error => {
                        alert(\'Error: \' + error.message);
                    });
            }
        }
    </script>
</body>
</html>';

file_put_contents("{$adminAppPath}/resources/views/admin/dashboard.blade.php", $dashboardView);
echo "✅ Created admin dashboard view\n";

// 5. Instructions
echo "\n🎉 Larabus Management Setup Complete!\n\n";
echo "📋 Next Steps:\n";
echo "1. Run migrations: php artisan migrate --database=management_mysql\n";
echo "2. Create first admin user (add to your routes or run manually)\n";
echo "3. For development: cd ../{$adminDomain} && php -S localhost:8080 router.php\n";
echo "4. Visit: http://localhost:8080\n\n";

echo "🔧 Production Setup:\n";
echo "1. Point admin.yourdomain.com to the {$adminDomain}/ folder\n";
echo "2. Set up proper database credentials in config.php\n";
echo "3. Configure Git repositories for your apps\n";
echo "4. Set up webhooks for auto-deployment\n\n";

echo "🚍 Happy managing with Larabus!\n";
