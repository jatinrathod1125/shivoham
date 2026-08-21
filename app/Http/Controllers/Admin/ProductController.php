<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a paginated listing of grocery products with multi-facet filters.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'brand']);

        // Search Filter (Name, SKU, Barcode, Description)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Brand Filter
        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        // Stock Status Filter
        if ($stockStatus = $request->input('stock_status')) {
            if ($stockStatus === 'in_stock') {
                $query->whereColumn('stock_quantity', '>', 'min_stock_threshold');
            } elseif ($stockStatus === 'low_stock') {
                $query->where('stock_quantity', '>', 0)
                      ->whereColumn('stock_quantity', '<=', 'min_stock_threshold');
            } elseif ($stockStatus === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
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

        // On Sale Filter
        if ($request->filled('on_sale')) {
            $now = now();
            $query->whereNotNull('special_price')
                  ->where(function ($q) use ($now) {
                      $q->whereNull('special_price_start')
                        ->orWhere('special_price_start', '<=', $now);
                  })
                  ->where(function ($q) use ($now) {
                      $q->whereNull('special_price_end')
                        ->orWhere('special_price_end', '>=', $now);
                  });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_low':
                $query->orderBy('selling_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('selling_price', 'desc');
                break;
            case 'stock_low':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_high':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(15)->withQueryString();

        // Metrics Summary
        $stats = [
            'total' => Product::count(),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
            'total_value' => (float) Product::select(DB::raw('SUM(stock_quantity * cost_price) as total_val'))->value('total_val') ?: 0,
        ];

        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.index', [
            'title' => 'Products - ' . config('admin.name', 'Grocery Admin'),
            'products' => $products,
            'stats' => $stats,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $units = ['pcs', 'kg', 'g', 'pack', 'liter', 'ml', 'bunch', 'box', 'tray', 'bottle', 'can'];

        return view('admin.products.create', [
            'title' => 'Add Product - ' . config('admin.name', 'Grocery Admin'),
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Auto-generate slug if blank
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $baseSlug = $validated['slug'];
        $count = 1;
        while (Product::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        // Auto-generate SKU if blank
        if (empty($validated['sku'])) {
            $prefix = 'PRD-' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $validated['name']), 0, 4));
            $validated['sku'] = $prefix . '-' . rand(100, 999);
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('products', 'public');
            $validated['thumbnail'] = Storage::url($path);
        }

        // Handle gallery images upload
        if ($request->hasFile('images')) {
            $galleryPaths = [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $galleryPaths[] = Storage::url($path);
            }
            $validated['images'] = $galleryPaths;
        }

        $product = Product::create($validated);

        // Record initial inventory transaction if stock > 0
        if ($product->stock_quantity > 0) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => InventoryTransaction::TYPE_ADDITION,
                'quantity' => $product->stock_quantity,
                'previous_stock' => 0,
                'current_stock' => $product->stock_quantity,
                'reason' => 'Initial catalog intake on product creation',
                'reference_id' => 'INIT-' . strtoupper(Str::random(6)),
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('toast_success', "Product '{$product->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $units = ['pcs', 'kg', 'g', 'pack', 'liter', 'ml', 'bunch', 'box', 'tray', 'bottle', 'can'];
        $recentTransactions = $product->inventoryTransactions()->latest()->take(5)->get();

        return view('admin.products.edit', [
            'title' => 'Edit Product - ' . $product->name,
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $oldStock = $product->stock_quantity;
        $newStock = (int) $validated['stock_quantity'];

        // Auto-generate slug if blank
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $baseSlug = $validated['slug'];
        $count = 1;
        while (Product::where('slug', $validated['slug'])->where('id', '!=', $product->id)->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        // Handle thumbnail replacement
        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail && Str::startsWith($product->thumbnail, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $product->thumbnail);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('thumbnail')->store('products', 'public');
            $validated['thumbnail'] = Storage::url($path);
        }

        // Handle gallery images upload / append
        if ($request->hasFile('images')) {
            $existingGallery = $product->images ?: [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $existingGallery[] = Storage::url($path);
            }
            $validated['images'] = $existingGallery;
        }

        $product->update($validated);

        // Check if stock was altered in edit form & record audit log
        if ($oldStock !== $newStock) {
            $delta = $newStock - $oldStock;
            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => $delta > 0 ? InventoryTransaction::TYPE_ADDITION : InventoryTransaction::TYPE_DEDUCTION,
                'quantity' => abs($delta),
                'previous_stock' => $oldStock,
                'current_stock' => $newStock,
                'reason' => 'Stock quantity updated via Product Edit form',
                'reference_id' => 'EDIT-' . strtoupper(Str::random(6)),
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('toast_success', "Product '{$product->name}' updated successfully.");
    }

    /**
     * Remove the specified product from storage with order history protection.
     */
    public function destroy(Product $product): RedirectResponse|JsonResponse
    {
        $ordersCount = $product->orderItems()->count();

        if ($ordersCount > 0) {
            $msg = "Cannot delete product '{$product->name}'. It is referenced in {$ordersCount} customer order(s). You may mark it inactive instead.";
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('toast_error', $msg);
        }

        $name = $product->name;

        // Clean up thumbnail if stored locally
        if ($product->thumbnail && Str::startsWith($product->thumbnail, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $product->thumbnail);
            Storage::disk('public')->delete($oldPath);
        }

        // Delete inventory transactions associated with this product
        $product->inventoryTransactions()->delete();

        $product->delete();

        $successMsg = "Product '{$name}' deleted successfully.";

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('admin.products.index')->with('toast_success', $successMsg);
    }

    /**
     * Quick AJAX status toggle.
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json([
            'success' => true,
            'is_active' => $product->is_active,
            'message' => "Product '{$product->name}' status changed to " . ($product->is_active ? 'Active' : 'Inactive') . '.',
        ]);
    }

    /**
     * Quick AJAX featured toggle.
     */
    public function toggleFeatured(Product $product): JsonResponse
    {
        $product->is_featured = !$product->is_featured;
        $product->save();

        return response()->json([
            'success' => true,
            'is_featured' => $product->is_featured,
            'message' => "Product '{$product->name}' " . ($product->is_featured ? 'marked as featured.' : 'removed from featured.'),
        ]);
    }

    /**
     * Quick AJAX stock adjustment with inventory transaction log.
     */
    public function quickStockUpdate(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'adjustment_type' => ['required', 'in:set,add,subtract'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $type = $request->input('adjustment_type');
        $qty = (int) $request->input('quantity');
        $previousStock = $product->stock_quantity;
        $reason = $request->input('reason') ?: 'Manual stock adjustment from products table';

        if ($type === 'set') {
            $newStock = $qty;
            $transactionType = InventoryTransaction::TYPE_ADJUSTMENT;
            $delta = $newStock - $previousStock;
        } elseif ($type === 'add') {
            $newStock = $previousStock + $qty;
            $transactionType = InventoryTransaction::TYPE_ADDITION;
            $delta = $qty;
        } else {
            $newStock = max(0, $previousStock - $qty);
            $transactionType = InventoryTransaction::TYPE_DEDUCTION;
            $delta = -$qty;
        }

        $product->stock_quantity = $newStock;
        $product->save();

        // Audit log
        InventoryTransaction::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'type' => $transactionType,
            'quantity' => abs($delta),
            'previous_stock' => $previousStock,
            'current_stock' => $newStock,
            'reason' => $reason,
            'reference_id' => 'ADJ-' . strtoupper(Str::random(6)),
        ]);

        return response()->json([
            'success' => true,
            'new_stock' => $newStock,
            'stock_status' => $product->stock_status,
            'message' => "Stock for '{$product->name}' updated from {$previousStock} to {$newStock} {$product->unit}.",
        ]);
    }
}
