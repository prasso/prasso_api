<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SitePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SitePackageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'superadmin']);
    }

    public function manage()
    {
        try {
            $sites = Site::orderBy('site_name')->get();
            $packages = SitePackage::where('is_active', true)->get();
            
            return view('admin.site-packages.manage', [
                'sites' => $sites,
                'packages' => $packages,
                'site' => null
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching sites and packages: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch sites and packages'], 500);
        }
    }

    public function getSitePackages(Site $site)
    {
        try {
            $packages = $site->packages()->get();
            return response()->json($packages);
        } catch (\Exception $e) {
            Log::error('Error fetching site packages: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch site packages'], 500);
        }
    }

    public function index()
    {
        try {
            $packages = SitePackage::with('sites')->get();
            return response()->json($packages);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch packages'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            $package = SitePackage::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true
            ]);

            return response()->json($package, 201);
        } catch (\Exception $e) {
            Log::error('Error creating package: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create package'], 500);
        }
    }

    public function subscribeSite(Request $request, SitePackage $package)
    {
        try {
            $validated = $request->validate([
                'site_id' => 'required|exists:sites,id',
                'expires_at' => 'nullable|date'
            ]);

            $site = Site::findOrFail($validated['site_id']);
            
            $package->sites()->attach($site->id, [
                'subscribed_at' => now(),
                'expires_at' => $validated['expires_at'] ?? null,
                'is_active' => true
            ]);

            return response()->json(['message' => 'Site subscribed to package successfully']);
        } catch (\Exception $e) {
            Log::error('Error subscribing site to package: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to subscribe site to package'], 500);
        }
    }

    public function unsubscribeSite(Request $request, SitePackage $package)
    {
        try {
            $validated = $request->validate([
                'site_id' => 'required|exists:sites,id'
            ]);

            $site = Site::findOrFail($validated['site_id']);
            
            $package->sites()->updateExistingPivot($site->id, [
                'is_active' => false,
                'expires_at' => now()
            ]);

            return response()->json(['message' => 'Site unsubscribed from package successfully']);
        } catch (\Exception $e) {
            Log::error('Error unsubscribing site from package: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to unsubscribe site from package'], 500);
        }
    }
}
