<?php

namespace App\Admin\Services;

use App\Admin\Models\Site;
use App\Admin\Models\Deployment;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * App Deployment Service
 * 
 * Handles deployment of individual apps from separate repositories
 * when /apps/ folder is in .gitignore
 */
class AppDeployer
{
    /**
     * Deploy an app from its repository
     */
    public function deployApp($appName, $repoUrl, $branch = 'main')
    {
        $deployment = $this->createDeploymentRecord($appName);
        
        try {
            $appPath = base_path("apps/{$appName}");
            
            Log::info("Starting deployment for app: {$appName}");
            
            if (!is_dir($appPath)) {
                // Clone new app
                $this->cloneApp($repoUrl, $appName, $branch);
            } else {
                // Update existing app
                $this->updateApp($appPath, $branch);
            }
            
            // Verify app structure
            $this->verifyApp($appPath);
            
            // Run app-specific setup
            $this->runAppSetup($appName);
            
            $deployment->update(['status' => 'success']);
            Log::info("Successfully deployed app: {$appName}");
            
            return $deployment;
            
        } catch (\Exception $e) {
            $deployment->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            Log::error("Failed to deploy app {$appName}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Clone a new app from repository
     */
    private function cloneApp($repoUrl, $appName, $branch)
    {
        $commands = [
            "cd " . base_path(),
            "git clone -b {$branch} {$repoUrl} apps/{$appName}",
        ];
        
        foreach ($commands as $command) {
            exec($command, $output, $returnCode);
            if ($returnCode !== 0) {
                throw new \Exception("Failed to clone app: {$command}. Output: " . implode("\n", $output));
            }
        }
    }
    
    /**
     * Update existing app from repository
     */
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
                throw new \Exception("Failed to update app: {$command}. Output: " . implode("\n", $output));
            }
        }
    }
    
    /**
     * Verify app has required structure
     */
    private function verifyApp($appPath)
    {
        $requiredFiles = [
            'routes.php',
            'resources/views'
        ];
        
        foreach ($requiredFiles as $file) {
            $fullPath = "{$appPath}/{$file}";
            if (!file_exists($fullPath) && !is_dir($fullPath)) {
                throw new \Exception("Missing required app file/directory: {$file}");
            }
        }
        
        return true;
    }
    
    /**
     * Run app-specific setup (migrations, etc.)
     */
    private function runAppSetup($appName)
    {
        // Run app migrations if they exist
        $migrationPath = base_path("apps/{$appName}/database/migrations");
        
        if (is_dir($migrationPath)) {
            $domainConnection = config('larabus.domain_default_connection');
            
            if ($domainConnection) {
                $command = "php " . base_path('artisan') . 
                          " migrate --path=apps/{$appName}/database/migrations" .
                          " --database={$domainConnection}";
                
                exec($command, $output, $returnCode);
                
                if ($returnCode !== 0) {
                    Log::warning("App migration failed for {$appName}: " . implode("\n", $output));
                }
            }
        }
        
        // Run app-specific setup if setup.php exists
        $setupScript = base_path("apps/{$appName}/setup.php");
        if (file_exists($setupScript)) {
            include $setupScript;
        }
    }
    
    /**
     * Create deployment record
     */
    private function createDeploymentRecord($appName)
    {
        $site = Site::where('app_name', $appName)->first();
        
        return Deployment::create([
            'site_id' => $site ? $site->id : null,
            'app_name' => $appName,
            'status' => 'pending',
            'deployed_by' => auth()->id() ?? 1, // System user if no auth
            'deployed_at' => now()
        ]);
    }
    
    /**
     * Deploy multiple apps
     */
    public function deployMultipleApps($apps)
    {
        $results = [];
        
        foreach ($apps as $appConfig) {
            try {
                $result = $this->deployApp(
                    $appConfig['name'],
                    $appConfig['repository'],
                    $appConfig['branch'] ?? 'main'
                );
                $results[] = ['app' => $appConfig['name'], 'success' => true, 'deployment' => $result];
            } catch (\Exception $e) {
                $results[] = ['app' => $appConfig['name'], 'success' => false, 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }
    
    /**
     * Create app backup before deployment
     */
    public function backupApp($appName)
    {
        $appPath = base_path("apps/{$appName}");
        
        if (!is_dir($appPath)) {
            throw new \Exception("App not found: {$appName}");
        }
        
        $backupPath = storage_path("backups/apps/{$appName}");
        $backupFile = $backupPath . '/' . date('Y-m-d_H-i-s') . '.tar.gz';
        
        // Create backup directory
        File::makeDirectory($backupPath, 0755, true, true);
        
        // Create tar.gz backup
        $command = "tar -czf {$backupFile} -C " . base_path() . " apps/{$appName}";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Failed to create backup for app: {$appName}");
        }
        
        return $backupFile;
    }
}
