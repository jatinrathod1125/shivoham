<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a paginated listing of categories with search and filters.
     */
    public function index(Request $request): View
    {
        $query = Category::with(['parent', 'children'])
            ->withCount('products');

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Parent Category Filter
        if ($parentId = $request->input('parent_id')) {
            if ($parentId === 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
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
        $sortBy = $request->input('sort', 'sort_order');
        $sortDir = $request->input('direction', 'asc');
        if (in_array($sortBy, ['name', 'sort_order', 'created_at', 'products_count'])) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
        }

        $categories = $query->paginate(12)->withQueryString();

        // Metrics Summary
        $stats = [
            'total' => Category::count(),
            'root' => Category::whereNull('parent_id')->count(),
            'sub' => Category::whereNotNull('parent_id')->count(),
            'active' => Category::where('is_active', true)->count(),
        ];

        $rootCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.index', [
            'title' => 'Categories - ' . config('admin.name', 'Grocery Admin'),
            'categories' => $categories,
            'stats' => $stats,
            'rootCategories' => $rootCategories,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', [
            'title' => 'Add Category - ' . config('admin.name', 'Grocery Admin'),
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $count = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $validated['image'] = Storage::url($path);
        }

        $category = Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('toast_success', "Category '{$category->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        // Exclude current category and its children from parent candidates to prevent cyclic loops
        $excludeIds = $category->children()->pluck('id')->push($category->id)->toArray();

        $parentCategories = Category::whereNotIn('id', $excludeIds)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', [
            'title' => 'Edit Category - ' . $category->name,
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure unique slug (excluding current category)
        $baseSlug = $validated['slug'];
        $count = 1;
        while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        // Handle image upload & replace old file
        if ($request->hasFile('image')) {
            if ($category->image && Str::startsWith($category->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $category->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('categories', 'public');
            $validated['image'] = Storage::url($path);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('toast_success', "Category '{$category->name}' updated successfully.");
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse|JsonResponse
    {
        $productsCount = $category->products()->count();
        $childrenCount = $category->children()->count();

        if ($productsCount > 0) {
            $msg = "Cannot delete '{$category->name}'. It has {$productsCount} assigned product(s). Please reassign them first.";
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('toast_error', $msg);
        }

        if ($childrenCount > 0) {
            $msg = "Cannot delete '{$category->name}'. It has {$childrenCount} sub-category(s). Please reassign them first.";
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('toast_error', $msg);
        }

        $name = $category->name;

        // Delete image from disk if present
        if ($category->image && Str::startsWith($category->image, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $category->image);
            Storage::disk('public')->delete($oldPath);
        }

        $category->delete();

        $successMsg = "Category '{$name}' deleted successfully.";

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('admin.categories.index')->with('toast_success', $successMsg);
    }

    /**
     * Quick AJAX status toggle.
     */
    public function toggleStatus(Category $category): JsonResponse
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json([
            'success' => true,
            'is_active' => $category->is_active,
            'message' => "Category '{$category->name}' status changed to " . ($category->is_active ? 'Active' : 'Inactive') . '.',
        ]);
    }

    /**
     * Quick AJAX featured toggle.
     */
    public function toggleFeatured(Category $category): JsonResponse
    {
        $category->is_featured = !$category->is_featured;
        $category->save();

        return response()->json([
            'success' => true,
            'is_featured' => $category->is_featured,
            'message' => "Category '{$category->name}' " . ($category->is_featured ? 'marked as featured.' : 'removed from featured.'),
        ]);
    }
}
