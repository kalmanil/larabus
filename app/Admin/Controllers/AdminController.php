<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Admin\Models\Site;
use App\Admin\Models\SystemUser;
use App\Admin\Models\Deployment;
use App\Admin\Services\AppDeployer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $sitesCount = Site::count();
        $deploymentsCount = Deployment::count();
        $recentDeployments = Deployment::latest()->take(5)->get();
        
        return view('admin.dashboard', compact('sitesCount', 'deploymentsCount', 'recentDeployments'));
    }
    
    public function sites(): View
    {
        $sites = Site::all();
        return view('admin.sites.index', compact('sites'));
    }
    
    public function createSite(): View
    {
        return view('admin.sites.create');
    }
    
    public function storeSite(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:managed_sites',
            'git_repo' => 'required|string|max:500',
            'branch' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);
        
        Site::create($validated);
        
        return redirect()->route('admin.sites')->with('success', 'Site created successfully!');
    }
    
    public function editSite(Site $site): View
    {
        return view('admin.sites.edit', compact('site'));
    }
    
    public function updateSite(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:managed_sites,domain,' . $site->id,
            'git_repo' => 'required|string|max:500',
            'branch' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);
        
        $site->update($validated);
        
        return redirect()->route('admin.sites')->with('success', 'Site updated successfully!');
    }
    
    public function deleteSite(Site $site)
    {
        $site->delete();
        return redirect()->route('admin.sites')->with('success', 'Site deleted successfully!');
    }
    
    public function deploySite(Site $site)
    {
        try {
            $deployer = new AppDeployer();
            $result = $deployer->deploy($site);
            
            return response()->json([
                'success' => true,
                'message' => 'Deployment started successfully',
                'deployment_id' => $result['deployment_id'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deployment failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function deployments(): View
    {
        $deployments = Deployment::with('site')->latest()->paginate(20);
        return view('admin.deployments.index', compact('deployments'));
    }
    
    public function system(): View
    {
        return view('admin.system.index');
    }
    
    public function updateSettings(Request $request)
    {
        // Handle system settings updates
        return redirect()->route('admin.system')->with('success', 'Settings updated successfully!');
    }
    
    // API methods
    public function apiSites()
    {
        $sites = Site::all();
        return response()->json($sites);
    }
    
    public function apiDeployments()
    {
        $deployments = Deployment::with('site')->latest()->take(20)->get();
        return response()->json($deployments);
    }
    
    public function apiDeploySite(Site $site)
    {
        return $this->deploySite($site);
    }
}
