# 🚀 Larabus Management with Apps in GitIgnore

## Strategy 1: Separate App Repositories

### Repository Structure
```
Framework: kalmanil/larabus (framework only)
Apps: kalmanil/larabus-app-{name} (individual apps)
```

### Deployment Service
```php
// larabus/apps/admin/Services/AppDeployer.php
<?php
namespace App\Services;

class AppDeployer
{
    public function deployApp($appName, $repoUrl, $branch = 'main')
    {
        $appPath = base_path("apps/{$appName}");
        
        if (!is_dir($appPath)) {
            // Clone new app
            $this->cloneApp($repoUrl, $appName, $branch);
        } else {
            // Update existing app
            $this->updateApp($appPath, $branch);
        }
        
        return $this->verifyApp($appPath);
    }
    
    private function cloneApp($repoUrl, $appName, $branch)
    {
        $commands = [
            "cd " . base_path(),
            "git clone -b {$branch} {$repoUrl} apps/{$appName}",
        ];
        
        foreach ($commands as $command) {
            exec($command, $output, $returnCode);
            if ($returnCode !== 0) {
                throw new \Exception("Failed to clone app: {$command}");
            }
        }
    }
    
    private function updateApp($appPath, $branch)
    {
        $commands = [
            "cd {$appPath}",
            "git fetch origin",
            "git checkout {$branch}",
            "git pull origin {$branch}"
        ];
        
        foreach ($commands as $command) {
            exec($command, $output, $returnCode);
            if ($returnCode !== 0) {
                throw new \Exception("Failed to update app: {$command}");
            }
        }
    }
}
```

### Management Interface
```php
// Enhanced Site model with app repository management
class Site extends DomainModel
{
    protected $fillable = [
        'domain', 'app_name', 'site_title', 'theme_color', 
        'status', 'app_repository', 'app_branch'
    ];
    
    public function deployApp()
    {
        $deployer = new AppDeployer();
        return $deployer->deployApp(
            $this->app_name, 
            $this->app_repository, 
            $this->app_branch ?? 'main'
        );
    }
}
```

## Strategy 2: App Templates with Local Management

### Template-Based Creation
```php
class TemplateManager
{
    public function createAppFromTemplate($templateName, $appName, $config)
    {
        $templatePath = base_path("templates/apps/{$templateName}");
        $appPath = base_path("apps/{$appName}");
        
        // Copy template
        $this->copyDirectory($templatePath, $appPath);
        
        // Replace placeholders
        $this->processTemplateFiles($appPath, $config);
        
        // Initialize git in app folder
        $this->initializeAppGit($appPath);
        
        return $appPath;
    }
    
    private function processTemplateFiles($appPath, $config)
    {
        $files = $this->getAllPhpFiles($appPath);
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            
            foreach ($config as $key => $value) {
                $content = str_replace("{{" . strtoupper($key) . "}}", $value, $content);
            }
            
            file_put_contents($file, $content);
        }
    }
}
```

## Strategy 3: Remote App Storage

### Cloud Storage Integration
```php
class CloudAppManager
{
    public function deployFromCloud($appName, $cloudPath)
    {
        // Download from S3, Google Drive, etc.
        $tempPath = $this->downloadFromCloud($cloudPath);
        $appPath = base_path("apps/{$appName}");
        
        // Extract and install
        $this->extractAndInstall($tempPath, $appPath);
        
        return $appPath;
    }
    
    public function backupToCloud($appName)
    {
        $appPath = base_path("apps/{$appName}");
        $backupPath = $this->createBackup($appPath);
        
        return $this->uploadToCloud($backupPath, "backups/{$appName}");
    }
}
```

## Database Migration Management

### Per-App Migrations
```php
class AppMigrationManager
{
    public function runAppMigrations($appName)
    {
        $migrationPath = base_path("apps/{$appName}/database/migrations");
        
        if (!is_dir($migrationPath)) {
            return false;
        }
        
        $domainConnection = config('larabus.domain_default_connection');
        
        $command = "php " . base_path('artisan') . 
                  " migrate --path=apps/{$appName}/database/migrations" .
                  " --database={$domainConnection}";
        
        exec($command, $output, $returnCode);
        
        return $returnCode === 0;
    }
}
```

## Management Dashboard Routes

### Enhanced Management Routes
```php
// Management routes for app deployment
Route::prefix('apps')->group(function () {
    
    Route::get('/', function () {
        $sites = Site::with('deployments')->get();
        $availableApps = scandir(base_path('apps'));
        
        return view('admin.apps.index', compact('sites', 'availableApps'));
    });
    
    Route::post('/{site}/deploy-app', function (Site $site) {
        try {
            $result = $site->deployApp();
            return response()->json(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    });
    
    Route::post('/create-from-template', function () {
        $validated = request()->validate([
            'template_name' => 'required',
            'app_name' => 'required|alpha_dash',
            'config' => 'required|array'
        ]);
        
        $manager = new TemplateManager();
        $appPath = $manager->createAppFromTemplate(
            $validated['template_name'],
            $validated['app_name'],
            $validated['config']
        );
        
        return response()->json(['success' => true, 'app_path' => $appPath]);
    });
    
    Route::post('/{appName}/backup', function ($appName) {
        $manager = new CloudAppManager();
        $backupUrl = $manager->backupToCloud($appName);
        
        return response()->json(['backup_url' => $backupUrl]);
    });
});
```

## Webhook Integration

### Git Webhook Handler
```php
Route::post('/webhook/app-deploy', function () {
    $payload = request()->json()->all();
    
    // Extract repository and branch info
    $repoUrl = $payload['repository']['clone_url'] ?? null;
    $branch = $payload['ref'] ?? 'refs/heads/main';
    $branch = str_replace('refs/heads/', '', $branch);
    
    // Find corresponding site
    $site = Site::where('app_repository', $repoUrl)->first();
    
    if ($site && $site->app_branch === $branch) {
        $site->deployApp();
        
        return response()->json(['message' => 'App deployed successfully']);
    }
    
    return response()->json(['message' => 'No matching site found']);
});
```

## Security Considerations

### App Isolation
```php
class SecurityManager
{
    public function validateAppSecurity($appPath)
    {
        $checks = [
            'no_system_calls' => $this->checkForSystemCalls($appPath),
            'safe_file_operations' => $this->checkFileOperations($appPath),
            'no_sensitive_data' => $this->checkForSensitiveData($appPath),
        ];
        
        return array_filter($checks) === $checks; // All must be true
    }
    
    private function checkForSystemCalls($appPath)
    {
        $dangerousFunctions = ['exec', 'shell_exec', 'system', 'passthru'];
        $files = $this->getAllPhpFiles($appPath);
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            foreach ($dangerousFunctions as $func) {
                if (strpos($content, $func . '(') !== false) {
                    return false;
                }
            }
        }
        
        return true;
    }
}
```
