<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Brand\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    /**
     * Display a paginated listing of brands with search and filters.
     */
    public function index(Request $request): View
    {
        $query = Brand::withCount('products');

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('website', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Featured Filter
        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Sorting
        $sortBy = $request->input('sort', 'name');
        $sortDir = $request->input('direction', 'asc');
        if (in_array($sortBy, ['name', 'created_at', 'products_count'])) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $brands = $query->paginate(12)->withQueryString();

        // Metrics Summary
        $stats = [
            'total' => Brand::count(),
            'featured' => Brand::where('is_featured', true)->count(),
            'active' => Brand::where('is_active', true)->count(),
            'inactive' => Brand::where('is_active', false)->count(),
        ];

        return view('admin.brands.index', [
            'title' => 'Brands - ' . config('admin.name', 'Grocery Admin'),
            'brands' => $brands,
            'stats' => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create(): View
    {
        return view('admin.brands.create', [
            'title' => 'Add Brand - ' . config('admin.name', 'Grocery Admin'),
        ]);
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $count = 1;
        while (Brand::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands', 'public');
            $validated['logo'] = Storage::url($path);
        }

        $brand = Brand::create($validated);

        return redirect()->route('admin.brands.index')
            ->with('toast_success', "Brand '{$brand->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', [
            'title' => 'Edit Brand - ' . $brand->name,
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $count = 1;
        while (Brand::where('slug', $validated['slug'])->where('id', '!=', $brand->id)->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        // Handle logo upload & replace old file
        if ($request->hasFile('logo')) {
            if ($brand->logo && Str::startsWith($brand->logo, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $brand->logo);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('logo')->store('brands', 'public');
            $validated['logo'] = Storage::url($path);
        }

        $brand->update($validated);

        return redirect()->route('admin.brands.index')
            ->with('toast_success', "Brand '{$brand->name}' updated successfully.");
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy(Brand $brand): RedirectResponse|JsonResponse
    {
        $productsCount = $brand->products()->count();

        if ($productsCount > 0) {
            $msg = "Cannot delete '{$brand->name}'. It has {$productsCount} assigned product(s). Please reassign them first.";
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('toast_error', $msg);
        }

        $name = $brand->name;

        // Delete logo file if present
        if ($brand->logo && Str::startsWith($brand->logo, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $brand->logo);
            Storage::disk('public')->delete($oldPath);
        }

        $brand->delete();

        $successMsg = "Brand '{$name}' deleted successfully.";

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('admin.brands.index')->with('toast_success', $successMsg);
    }

    /**
     * Quick AJAX status toggle.
     */
    public function toggleStatus(Brand $brand): JsonResponse
    {
        $brand->is_active = !$brand->is_active;
        $brand->save();

        return response()->json([
            'success' => true,
            'is_active' => $brand->is_active,
            'message' => "Brand '{$brand->name}' status changed to " . ($brand->is_active ? 'Active' : 'Inactive') . '.',
        ]);
    }

    /**
     * Quick AJAX featured toggle.
     */
    public function toggleFeatured(Brand $brand): JsonResponse
    {
        $brand->is_featured = !$brand->is_featured;
        $brand->save();

        return response()->json([
            'success' => true,
            'is_featured' => $brand->is_featured,
            'message' => "Brand '{$brand->name}' " . ($brand->is_featured ? 'marked as featured.' : 'removed from featured.'),
        ]);
    }
}
