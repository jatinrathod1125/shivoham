<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StoreInventoryAdjustmentRequest;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display current stock levels overview and low stock alerts.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'brand']);

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
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

        // Sorting
        $sort = $request->input('sort', 'stock_asc');
        switch ($sort) {
            case 'stock_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'value_desc':
                $query->orderByRaw('(stock_quantity * cost_price) desc');
                break;
            default:
                $query->orderBy('stock_quantity', 'asc');
                break;
        }

        $products = $query->paginate(15)->withQueryString();

        // Metrics Summary
        $stats = [
            'total_skus' => Product::count(),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
            'total_units' => (int) Product::sum('stock_quantity'),
            'total_value' => (float) Product::select(DB::raw('SUM(stock_quantity * cost_price) as total_val'))->value('total_val') ?: 0,
        ];

        // Priority Low Stock Alert list (Top 4 critical items)
        $priorityLowStock = Product::lowStock()
            ->orderBy('stock_quantity', 'asc')
            ->take(4)
            ->get();

        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();

        return view('admin.inventory.index', [
            'title' => 'Inventory Management - ' . config('admin.name', 'Grocery Admin'),
            'products' => $products,
            'stats' => $stats,
            'priorityLowStock' => $priorityLowStock,
            'categories' => $categories,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Display the inventory audit history ledger.
     */
    public function history(Request $request): View
    {
        $query = InventoryTransaction::with(['product.category', 'user'])->latest();

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Transaction Type Filter
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Product Filter
        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        $transactions = $query->paginate(20)->withQueryString();

        // History Stats
        $historyStats = [
            'total' => InventoryTransaction::count(),
            'additions' => InventoryTransaction::where('type', InventoryTransaction::TYPE_ADDITION)->count(),
            'orders' => InventoryTransaction::where('type', InventoryTransaction::TYPE_ORDER)->count(),
            'adjustments' => InventoryTransaction::whereIn('type', [InventoryTransaction::TYPE_ADJUSTMENT, InventoryTransaction::TYPE_DEDUCTION])->count(),
        ];

        return view('admin.inventory.history', [
            'title' => 'Inventory Audit History - ' . config('admin.name', 'Grocery Admin'),
            'transactions' => $transactions,
            'historyStats' => $historyStats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Store an inventory stock adjustment with audit transaction record.
     */
    public function adjust(StoreInventoryAdjustmentRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);

        $type = $validated['type'];
        $qty = (int) $validated['quantity'];
        $previousStock = $product->stock_quantity;
        $reason = (!empty($validated['reason'])) ? $validated['reason'] : 'Manual inventory adjustment';
        $ref = (!empty($validated['reference_id'])) ? $validated['reference_id'] : ('ADJ-' . strtoupper(Str::random(6)));

        if ($type === InventoryTransaction::TYPE_ADDITION) {
            $newStock = $previousStock + $qty;
            $recordedQty = $qty;
        } elseif ($type === InventoryTransaction::TYPE_DEDUCTION) {
            $newStock = max(0, $previousStock - $qty);
            $recordedQty = $qty;
        } else { // TYPE_ADJUSTMENT (Exact count set)
            $newStock = $qty;
            $recordedQty = abs($newStock - $previousStock);
        }

        $product->stock_quantity = $newStock;
        $product->save();

        // Audit Record
        $transaction = InventoryTransaction::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity' => $recordedQty,
            'previous_stock' => $previousStock,
            'current_stock' => $newStock,
            'reason' => $reason,
            'reference_id' => $ref,
        ]);

        $msg = "Stock for '{$product->name}' adjusted from {$previousStock} to {$newStock} {$product->unit}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'new_stock' => $newStock,
                'stock_status' => $product->stock_status,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('toast_success', $msg);
    }
}
