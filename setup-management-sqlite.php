<?php

/**
 * Larabus Management Setup Script (SQLite Version)
 * 
 * Sets up the central management system using the existing central SQLite database
 */

echo "🚍 Larabus Management Setup (SQLite)\n";
echo "=====================================\n\n";

// Check if we're in the right directory
if (!file_exists('composer.json') || !is_dir('apps')) {
    echo "❌ Error: Please run this script from the Larabus root directory.\n";
    exit(1);
}

// Check if central database exists
$centralDb = 'database/database.sqlite';
if (!file_exists($centralDb)) {
    echo "❌ Error: Central SQLite database not found at {$centralDb}\n";
    echo "Please run 'php artisan migrate' first to create the central database.\n";
    exit(1);
}

echo "✅ Found central SQLite database: {$centralDb}\n";

// 1. Create admin app if it doesn't exist
echo "📂 Setting up admin app...\n";

$adminAppPath = 'apps/admin';
if (!is_dir($adminAppPath)) {
    mkdir($adminAppPath, 0755, true);
    mkdir("{$adminAppPath}/Models", 0755, true);
    mkdir("{$adminAppPath}/Services", 0755, true);
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
    echo "✅ Created admin domain folder: {$adminDomain}\n";
} else {
    echo "ℹ️ Admin domain already exists\n";
}

// 3. Copy .htaccess if template exists
if (file_exists('templates/domain-htaccess.template')) {
    copy('templates/domain-htaccess.template', "{$domainPath}/.htaccess");
    echo "✅ Created .htaccess file\n";
}

// 4. Run management migrations
echo "🗄️ Running management migrations...\n";

// Run the new migration
$migrationCommand = "php artisan migrate --force";
exec($migrationCommand, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Management tables created in central SQLite database\n";
} else {
    echo "⚠️ Migration may have failed. Output:\n";
    echo implode("\n", $output) . "\n";
}

// 5. Create first admin user
echo "👤 Creating first admin user...\n";

$username = readline("Admin username [admin]: ") ?: 'admin';
$email = readline("Admin email [admin@larabus.dev]: ") ?: 'admin@larabus.dev';
$password = readline("Admin password [password]: ") ?: 'password';

// Create user via Laravel's tinker
$createUserCommand = "php artisan tinker --execute=\"
use App\Models\SystemUser;
try {
    \$user = SystemUser::create([
        'username' => '{$username}',
        'email' => '{$email}',
        'password' => bcrypt('{$password}'),
        'role' => 'admin',
        'is_active' => true
    ]);
    echo 'Admin user created successfully!';
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage();
}
\"";

exec($createUserCommand, $userOutput, $userReturnCode);

if ($userReturnCode === 0) {
    echo "✅ Admin user created: {$username} / {$email}\n";
} else {
    echo "⚠️ Admin user creation may have failed. You can create one manually later.\n";
}

// 6. Create basic admin dashboard view
echo "🎨 Creating admin dashboard view...\n";

$dashboardView = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteName ?? "Larabus Management" }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-active { @apply bg-green-100 text-green-800; }
        .status-maintenance { @apply bg-yellow-100 text-yellow-800; }
        .status-disabled { @apply bg-red-100 text-red-800; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-gray-800 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold">🚍 Larabus Management</h1>
                        <p class="text-gray-300">Central control panel for all your sites</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="/sites/create" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md text-sm font-medium">
                            ➕ New Site
                        </a>
                        <a href="/api/stats" class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                            📊 API
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="text-2xl">🌐</div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Sites</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats[\'total_sites\'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="text-2xl">✅</div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Sites</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats[\'active_sites\'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="text-2xl">🚀</div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Deployments</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats[\'total_deployments\'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="text-2xl">📊</div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Database</dt>
                                    <dd class="text-lg font-medium text-gray-900">SQLite</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sites Overview -->
            @if(isset($sites) && $sites->count() > 0)
            <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Sites Overview</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">All managed sites and their status</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    @foreach($sites as $site)
                    <li>
                        <div class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-indigo-600 truncate">
                                            <a href="/sites/{{ $site->id }}">{{ $site->domain }}</a>
                                        </p>
                                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-{{ $site->status }}">
                                            {{ ucfirst($site->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center text-sm text-gray-500">
                                        <span>{{ $site->app_name }}</span>
                                        <span class="mx-1">•</span>
                                        <span>{{ $site->site_title }}</span>
                                        @if($site->latestDeployment)
                                        <span class="mx-1">•</span>
                                        <span>Last deployed {{ $site->latestDeployment->deployed_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    @if($site->app_repository)
                                    <button onclick="deploySite({{ $site->id }})" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                        🚀 Deploy
                                    </button>
                                    @endif
                                    <a href="/sites/{{ $site->id }}/edit" 
                                       class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                                        ✏️ Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @else
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 text-6xl">🚍</div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No sites yet</h3>
                <p class="mt-2 text-sm text-gray-500">Get started by creating your first site!</p>
                <div class="mt-6">
                    <a href="/sites/create" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        ➕ Create Your First Site
                    </a>
                </div>
            </div>
            @endif
            
            <!-- Recent Deployments -->
            @if(isset($recentDeployments) && $recentDeployments->count() > 0)
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Deployments</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    @foreach($recentDeployments->take(5) as $deployment)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $deployment->app_name }}</p>
                                <p class="text-sm text-gray-500">{{ $deployment->deployed_at->diffForHumans() }}</p>
                            </div>
                            <div>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($deployment->status === \'success\') bg-green-100 text-green-800
                                    @elseif($deployment->status === \'failed\') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $deployment->status_icon }} {{ ucfirst($deployment->status) }}
                                </span>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </main>
    </div>
    
    <script>
        function deploySite(siteId) {
            if (confirm(\'Deploy this site? This will pull the latest code from the repository.\')) {
                const button = event.target;
                button.disabled = true;
                button.textContent = \'⏳ Deploying...\';
                
                fetch(`/deploy/site/${siteId}`, { 
                    method: \'POST\',
                    headers: {
                        \'X-CSRF-TOKEN\': document.querySelector(\'meta[name="csrf-token"]\')?.getAttribute(\'content\') || \'\'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(\'✅ Deployment started successfully!\');
                        location.reload();
                    } else {
                        alert(\'❌ Deployment failed: \' + data.message);
                        button.disabled = false;
                        button.textContent = \'🚀 Deploy\';
                    }
                })
                .catch(error => {
                    alert(\'❌ Error: \' + error.message);
                    button.disabled = false;
                    button.textContent = \'🚀 Deploy\';
                });
            }
        }
    </script>
</body>
</html>';

file_put_contents("{$adminAppPath}/resources/views/admin/dashboard.blade.php", $dashboardView);
echo "✅ Created admin dashboard view\n";

echo "\n🎉 Larabus Management Setup Complete (SQLite)!\n\n";

echo "✅ **Advantages of using central SQLite:**\n";
echo "   • Simplified architecture - one database for framework + management\n";
echo "   • No additional MySQL setup required\n";
echo "   • Framework data (sessions, cache) + management data in one place\n";
echo "   • Easier backup and maintenance\n";
echo "   • Perfect for small to medium deployments\n\n";

echo "📋 **Next Steps:**\n";
echo "1. Start admin interface: cd ../admin.larabus.dev && php -S localhost:8080 router.php\n";
echo "2. Visit: http://localhost:8080\n";
echo "3. Login with: {$username} / {$password}\n";
echo "4. Create your first managed site!\n\n";

echo "🔧 **For production:**\n";
echo "1. Point admin.yourdomain.dev to the admin.larabus.dev/ folder\n";
echo "2. Set up Git repositories for your apps\n";
echo "3. Configure webhooks for auto-deployment\n\n";

echo "🗄️ **Database architecture:**\n";
echo "   • Central SQLite: Framework data + Management data\n";
echo "   • App MySQL: Individual app data (users, content, etc.)\n";
echo "   • Clean separation between framework and application concerns\n\n";

echo "🚍 Happy managing with Larabus!\n";
