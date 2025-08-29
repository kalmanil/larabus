<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Larabus management system
     * These tables will be created in the central SQLite database
     */
    public function up(): void
    {
        // System users table (for managing Larabus)
        Schema::create('system_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 50)->default('developer'); // admin, manager, developer
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['role', 'is_active']);
        });

        // Managed sites registry
        Schema::create('managed_sites', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->string('app_name', 100)->unique();
            $table->string('site_title');
            $table->string('theme_color', 7)->default('#6366f1');
            $table->string('status', 20)->default('active'); // active, maintenance, disabled
            $table->string('app_repository')->nullable();
            $table->string('app_branch', 100)->default('main');
            $table->boolean('auto_deploy')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'auto_deploy']);
            $table->index('app_name');
        });

        // Deployment history
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('managed_sites')->onDelete('cascade');
            $table->string('app_name', 100); // Store app name even if site is deleted
            $table->string('git_commit', 40)->nullable();
            $table->string('status', 20)->default('pending'); // pending, success, failed
            $table->foreignId('deployed_by')->nullable()->constrained('system_users')->onDelete('set null');
            $table->timestamp('deployed_at');
            $table->text('error_message')->nullable();
            $table->text('deployment_notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'deployed_at']);
            $table->index(['app_name', 'deployed_at']);
            $table->index('deployed_at');
        });

        // App templates (for creating new apps)
        Schema::create('app_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('branch', 100)->default('main');
            $table->text('default_config')->nullable(); // JSON string for default configuration
            $table->text('required_fields')->nullable(); // JSON string for required fields
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active', 'name']);
        });

        // System settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string'); // string, json, boolean, integer
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be accessed by non-admin users
            $table->timestamps();
            
            $table->index(['key', 'is_public']);
        });

        // Deployment logs (detailed logs for each deployment)
        Schema::create('deployment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deployment_id')->constrained('deployments')->onDelete('cascade');
            $table->string('level', 20); // info, warning, error, debug
            $table->text('message');
            $table->text('context')->nullable(); // JSON string for additional context data
            $table->timestamp('logged_at');
            
            $table->index(['deployment_id', 'level']);
            $table->index('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment_logs');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('app_templates');
        Schema::dropIfExists('deployments');
        Schema::dropIfExists('managed_sites');
        Schema::dropIfExists('system_users');
    }
};
