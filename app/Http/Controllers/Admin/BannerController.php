<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\StoreBannerRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerRequest;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BannerController extends Controller
{
    /**
     * Display a paginated listing of promotional banners with position filters.
     */
    public function index(Request $request): View
    {
        $query = Banner::query();

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%")
                  ->orWhere('link', 'like', "%{$search}%");
            });
        }

        // Position Filter
        if ($position = $request->input('position')) {
            $query->where('position', $position);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Sorting
        $sort = $request->input('sort', 'sort_asc');
        switch ($sort) {
            case 'sort_asc':
                $query->orderBy('sort_order', 'asc')->latest();
                break;
            case 'latest':
                $query->latest();
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            default:
                $query->orderBy('sort_order', 'asc')->latest();
                break;
        }

        $banners = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Banner::count(),
            'active' => Banner::where('is_active', true)->count(),
            'hero' => Banner::where('position', Banner::POSITION_HOME_HERO)->count(),
            'promo' => Banner::whereIn('position', [Banner::POSITION_PROMOTIONAL_BAR, Banner::POSITION_CATEGORY_TOP])->count(),
        ];

        return view('admin.banners.index', [
            'title' => 'Banners - ' . config('admin.name', 'Grocery Admin'),
            'banners' => $banners,
            'stats' => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create(): View
    {
        return view('admin.banners.create', [
            'title' => 'Add Banner - ' . config('admin.name', 'Grocery Admin'),
        ]);
    }

    /**
     * Store a newly created banner in storage.
     */
    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $validated['image'] = Storage::url($path);
        }

        $banner = Banner::create($validated);

        return redirect()->route('admin.banners.index')
            ->with('toast_success', "Banner '{$banner->title}' created successfully.");
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', [
            'title' => 'Edit Banner - ' . $banner->title,
            'banner' => $banner,
        ]);
    }

    /**
     * Update the specified banner in storage.
     */
    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($banner->image && Str::startsWith($banner->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $banner->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('banners', 'public');
            $validated['image'] = Storage::url($path);
        }

        $banner->update($validated);

        return redirect()->route('admin.banners.index')
            ->with('toast_success', "Banner '{$banner->title}' updated successfully.");
    }

    /**
     * Remove the specified banner from storage.
     */
    public function destroy(Banner $banner): RedirectResponse|JsonResponse
    {
        $title = $banner->title;

        if ($banner->image && Str::startsWith($banner->image, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $banner->image);
            Storage::disk('public')->delete($oldPath);
        }

        $banner->delete();

        $successMsg = "Banner '{$title}' deleted successfully.";

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('admin.banners.index')->with('toast_success', $successMsg);
    }

    /**
     * Quick AJAX status toggle.
     */
    public function toggleStatus(Banner $banner): JsonResponse
    {
        $banner->is_active = !$banner->is_active;
        $banner->save();

        return response()->json([
            'success' => true,
            'is_active' => $banner->is_active,
            'message' => "Banner '{$banner->title}' status changed to " . ($banner->is_active ? 'Active' : 'Inactive') . '.',
        ]);
    }
}
