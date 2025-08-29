<?php

use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\AdminController;
use App\Admin\Models\Site;
use App\Admin\Models\SystemUser;
use App\Admin\Models\Deployment;

// Admin routes - only accessible from admin domain
Route::middleware(['web'])->group(function () {
    
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Sites management
    Route::get('/sites', [AdminController::class, 'sites'])->name('admin.sites');
    Route::get('/sites/create', [AdminController::class, 'createSite'])->name('admin.sites.create');
    Route::post('/sites', [AdminController::class, 'storeSite'])->name('admin.sites.store');
    Route::get('/sites/{site}/edit', [AdminController::class, 'editSite'])->name('admin.sites.edit');
    Route::put('/sites/{site}', [AdminController::class, 'updateSite'])->name('admin.sites.update');
    Route::delete('/sites/{site}', [AdminController::class, 'deleteSite'])->name('admin.sites.delete');
    
    // Deployment
    Route::post('/sites/{site}/deploy', [AdminController::class, 'deploySite'])->name('admin.sites.deploy');
    Route::get('/deployments', [AdminController::class, 'deployments'])->name('admin.deployments');
    
    // System settings
    Route::get('/system', [AdminController::class, 'system'])->name('admin.system');
    Route::post('/system/settings', [AdminController::class, 'updateSettings'])->name('admin.system.settings');
    
    // API endpoints
    Route::prefix('api')->group(function () {
        Route::get('/sites', [AdminController::class, 'apiSites']);
        Route::get('/deployments', [AdminController::class, 'apiDeployments']);
        Route::post('/sites/{site}/deploy', [AdminController::class, 'apiDeploySite']);
    });
});
