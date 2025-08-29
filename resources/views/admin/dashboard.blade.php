<!DOCTYPE html>
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
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['total_sites'] ?? 0 }}</dd>
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
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['active_sites'] ?? 0 }}</dd>
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
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['total_deployments'] ?? 0 }}</dd>
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
                                    @if($deployment->status === 'success') bg-green-100 text-green-800
                                    @elseif($deployment->status === 'failed') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($deployment->status) }}
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
            if (confirm('Deploy this site? This will pull the latest code from the repository.')) {
                const button = event.target;
                button.disabled = true;
                button.textContent = '⏳ Deploying...';
                
                fetch(`/deploy/site/${siteId}`, { 
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Deployment started successfully!');
                        location.reload();
                    } else {
                        alert('❌ Deployment failed: ' + data.message);
                        button.disabled = false;
                        button.textContent = '🚀 Deploy';
                    }
                })
                .catch(error => {
                    alert('❌ Error: ' + error.message);
                    button.disabled = false;
                    button.textContent = '🚀 Deploy';
                });
            }
        }
    </script>
</body>
</html>
