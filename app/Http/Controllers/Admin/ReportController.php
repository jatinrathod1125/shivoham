<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display comprehensive business analytics, revenue trends, and category breakdown.
     */
    public function index(Request $request): View
    {
        $range = $request->input('range', '30_days');
        [$startDate, $endDate, $prevStartDate, $prevEndDate, $rangeLabel] = $this->resolveDateRanges($range, $request);

        // Active Period Orders Query
        $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate]);
        $paidOrdersQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', Order::PAYMENT_PAID);

        // Previous Period Query for Growth Comparison
        $prevPaidOrdersQuery = Order::whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->where('payment_status', Order::PAYMENT_PAID);

        // Core Financial KPIs
        $grossRevenue = (float) $paidOrdersQuery->sum('total');
        $prevGrossRevenue = (float) $prevPaidOrdersQuery->sum('total');
        $revenueGrowth = $prevGrossRevenue > 0
            ? round((($grossRevenue - $prevGrossRevenue) / $prevGrossRevenue) * 100, 1)
            : 0;

        $totalOrdersCount = (int) $ordersQuery->count();
        $completedOrdersCount = (int) $paidOrdersQuery->count();
        $prevCompletedOrders = (int) $prevPaidOrdersQuery->count();
        $orderGrowth = $prevCompletedOrders > 0
            ? round((($completedOrdersCount - $prevCompletedOrders) / $prevCompletedOrders) * 100, 1)
            : 0;

        $aov = $completedOrdersCount > 0 ? round($grossRevenue / $completedOrdersCount, 2) : 0;
        $totalItemsSold = (int) OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate])->where('payment_status', Order::PAYMENT_PAID);
        })->sum('quantity');
        $avgItemsPerBasket = $completedOrdersCount > 0 ? round($totalItemsSold / $completedOrdersCount, 1) : 0;

        // Estimated Cost & Profit
        $totalCostOfGoods = (float) OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate])->where('payment_status', Order::PAYMENT_PAID);
        })
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->select(DB::raw('SUM(order_items.quantity * products.cost_price) as total_cost'))
        ->value('total_cost') ?: 0;

        $grossProfit = max(0, $grossRevenue - $totalCostOfGoods);
        $profitMargin = $grossRevenue > 0 ? round(($grossProfit / $grossRevenue) * 100, 1) : 0;

        // Daily Trend Timeline Series (Chart 1)
        $dailyData = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', Order::PAYMENT_PAID)
            ->select(
                DB::raw('DATE(created_at) as date_val'),
                DB::raw('SUM(total) as daily_total'),
                DB::raw('COUNT(id) as daily_orders')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date_val', 'asc')
            ->get();

        $chartDates = [];
        $chartRevenues = [];
        $chartOrders = [];

        // Build continuous date intervals
        $periodCursor = clone $startDate;
        $dailyMap = $dailyData->keyBy('date_val');

        while ($periodCursor->lte($endDate)) {
            $dateKey = $periodCursor->format('Y-m-d');
            $chartDates[] = $periodCursor->format('M d');
            $record = $dailyMap->get($dateKey);
            $chartRevenues[] = $record ? round((float) $record->daily_total, 2) : 0;
            $chartOrders[] = $record ? (int) $record->daily_orders : 0;
            $periodCursor->addDay();
        }

        // Category Revenue Breakdown (Chart 2)
        $categoryBreakdown = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.payment_status', Order::PAYMENT_PAID)
            ->select(
                DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'),
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.total) as total_revenue')
            )
            ->groupBy('category_name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $catLabels = $categoryBreakdown->pluck('category_name')->toArray();
        $catRevenues = $categoryBreakdown->pluck('total_revenue')->map(fn($v) => round((float)$v, 2))->toArray();

        // Payment Methods Distribution (Chart 3)
        $paymentMethods = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'payment_method',
                DB::raw('COUNT(id) as count_orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('payment_method')
            ->get();

        $payLabels = $paymentMethods->map(fn($p) => ucwords(str_replace('_', ' ', $p->payment_method)))->toArray();
        $payCounts = $paymentMethods->pluck('count_orders')->toArray();

        // Top 10 Best Selling Grocery Products
        $topProducts = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate])->where('payment_status', Order::PAYMENT_PAID);
        })
        ->with('product.category')
        ->select(
            'product_id',
            'product_name',
            'sku',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(total) as total_sales'),
            DB::raw('AVG(unit_price) as avg_price')
        )
        ->groupBy('product_id', 'product_name', 'sku')
        ->orderBy('total_sales', 'desc')
        ->take(10)
        ->get();

        // Top VIP Customer Spenders
        $topCustomers = Customer::withCount(['orders' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->orderBy('total_spent', 'desc')
        ->take(10)
        ->get();

        return view('admin.reports.index', [
            'title' => 'Analytics & Reports - ' . config('admin.name', 'Grocery Admin'),
            'range' => $range,
            'rangeLabel' => $rangeLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'kpis' => [
                'gross_revenue' => $grossRevenue,
                'revenue_growth' => $revenueGrowth,
                'total_orders' => $totalOrdersCount,
                'completed_orders' => $completedOrdersCount,
                'order_growth' => $orderGrowth,
                'aov' => $aov,
                'items_sold' => $totalItemsSold,
                'avg_basket' => $avgItemsPerBasket,
                'gross_profit' => $grossProfit,
                'profit_margin' => $profitMargin,
            ],
            'chartDates' => json_encode($chartDates),
            'chartRevenues' => json_encode($chartRevenues),
            'chartOrders' => json_encode($chartOrders),
            'catLabels' => json_encode($catLabels),
            'catRevenues' => json_encode($catRevenues),
            'payLabels' => json_encode($payLabels),
            'payCounts' => json_encode($payCounts),
            'categoryBreakdown' => $categoryBreakdown,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
        ]);
    }

    /**
     * Display advanced inventory turnover velocity, low stock forecasting, and dead stock analysis.
     */
    public function inventory(Request $request): View
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Catalog Valuations
        $totalSkus = Product::count();
        $totalUnitsOnHand = (int) Product::sum('stock_quantity');
        $retailValue = (float) DB::table('products')->selectRaw('SUM(stock_quantity * selling_price) as total_val')->value('total_val') ?: 0;
        $costValue = (float) DB::table('products')->selectRaw('SUM(stock_quantity * cost_price) as total_cost')->value('total_cost') ?: 0;

        // Stock Health Status Counters
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();
        $lowStockCount = Product::where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock_threshold')
            ->count();
        $healthyStockCount = max(0, $totalSkus - $outOfStockCount - $lowStockCount);

        // 30-Day Sales Velocity per Product
        $sales30d = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $thirtyDaysAgo)
            ->where('orders.payment_status', Order::PAYMENT_PAID)
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as units_sold_30d')
            )
            ->groupBy('order_items.product_id')
            ->pluck('units_sold_30d', 'product_id');

        // All Products with Velocity & Days of Inventory Remaining (DOIR)
        $allProducts = Product::with('category', 'brand')->get()->map(function ($p) use ($sales30d) {
            $sold30 = (int) ($sales30d[$p->id] ?? 0);
            $dailyVelocity = round($sold30 / 30, 2);
            $doir = $dailyVelocity > 0 ? round($p->stock_quantity / $dailyVelocity, 1) : ($p->stock_quantity > 0 ? 999 : 0);

            // Reorder Urgency Level
            if ($p->stock_quantity <= 0) {
                $urgency = 'Critical Out of Stock';
                $urgencyColor = 'rose';
            } elseif ($doir <= 7 || $p->stock_quantity <= $p->min_stock_threshold) {
                $urgency = 'Urgent Reorder (< 7 days)';
                $urgencyColor = 'amber';
            } elseif ($doir <= 14) {
                $urgency = 'Moderate (< 14 days)';
                $urgencyColor = 'sky';
            } else {
                $urgency = 'Healthy Supply';
                $urgencyColor = 'emerald';
            }

            $p->units_sold_30d = $sold30;
            $p->daily_velocity = $dailyVelocity;
            $p->doir = $doir;
            $p->urgency = $urgency;
            $p->urgency_color = $urgencyColor;
            $p->tied_up_capital = round($p->stock_quantity * $p->cost_price, 2);

            return $p;
        });

        // Fast Moving Items (High 30d Sales Velocity)
        $fastMoving = $allProducts->filter(fn($p) => $p->units_sold_30d > 0)
            ->sortByDesc('units_sold_30d')
            ->take(10)
            ->values();

        // Slow Moving / Dead Stock (0 sales in past 30 days, high stock on hand)
        $slowMoving = $allProducts->filter(fn($p) => $p->units_sold_30d === 0 && $p->stock_quantity > 10)
            ->sortByDesc('tied_up_capital')
            ->take(10)
            ->values();

        // Category Inventory Value Matrix
        $categoryInventory = Category::withCount('products')
            ->with(['products' => function ($q) {
                $q->select('id', 'category_id', 'stock_quantity', 'cost_price', 'selling_price');
            }])
            ->get()
            ->map(function ($cat) {
                $units = $cat->products->sum('stock_quantity');
                $val = $cat->products->sum(fn($p) => $p->stock_quantity * $p->selling_price);
                return [
                    'name' => $cat->name,
                    'skus' => $cat->products_count,
                    'units' => $units,
                    'retail_value' => round($val, 2),
                ];
            })
            ->sortByDesc('retail_value')
            ->values();

        return view('admin.reports.inventory', [
            'title' => 'Inventory Velocity & Health - ' . config('admin.name', 'Grocery Admin'),
            'kpis' => [
                'total_skus' => $totalSkus,
                'total_units' => $totalUnitsOnHand,
                'retail_value' => $retailValue,
                'cost_value' => $costValue,
                'out_of_stock' => $outOfStockCount,
                'low_stock' => $lowStockCount,
                'healthy_stock' => $healthyStockCount,
            ],
            'fastMoving' => $fastMoving,
            'slowMoving' => $slowMoving,
            'categoryInventory' => $categoryInventory,
            'stockHealthSeries' => json_encode([$healthyStockCount, $lowStockCount, $outOfStockCount]),
            'catNames' => json_encode($categoryInventory->pluck('name')->toArray()),
            'catValues' => json_encode($categoryInventory->pluck('retail_value')->toArray()),
        ]);
    }

    /**
     * Stream CSV export of performance analytics and itemized sales report.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $range = $request->input('range', '30_days');
        [$startDate, $endDate] = $this->resolveDateRanges($range, $request);

        $filename = 'grocery_sales_report_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            // Header Section
            fputcsv($handle, ['Fresh Groceries Hub - Sales Analytics Report']);
            fputcsv($handle, ['Report Range', $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y')]);
            fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            // Itemized Orders Header
            fputcsv($handle, ['Order Number', 'Date', 'Customer', 'Payment Status', 'Payment Method', 'Subtotal', 'Tax', 'Shipping', 'Discount', 'Total ($)']);

            $orders = Order::whereBetween('created_at', [$startDate, $endDate])->latest()->get();
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->created_at->format('Y-m-d H:i'),
                    $order->customer_name,
                    $order->payment_status,
                    $order->payment_method,
                    number_format($order->subtotal, 2),
                    number_format($order->tax, 2),
                    number_format($order->shipping_fee, 2),
                    number_format($order->discount, 2),
                    number_format($order->total, 2),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Stream CSV export of complete inventory velocity and forecasting health.
     */
    public function exportInventoryCsv(Request $request): StreamedResponse
    {
        $filename = 'grocery_inventory_health_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Fresh Groceries Hub - Inventory Health & Velocity Report']);
            fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['SKU', 'Product Name', 'Category', 'Stock On Hand', 'Min Threshold', 'Cost Price ($)', 'Selling Price ($)', 'Total Cost Value ($)', 'Total Retail Value ($)', '30-Day Sales', 'Daily Velocity', 'Days of Inventory Remaining (DOIR)', 'Status']);

            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $sales30d = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.created_at', '>=', $thirtyDaysAgo)
                ->where('orders.payment_status', Order::PAYMENT_PAID)
                ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as sold'))
                ->groupBy('order_items.product_id')
                ->pluck('sold', 'product_id');

            $products = Product::with('category')->get();

            foreach ($products as $p) {
                $sold = (int) ($sales30d[$p->id] ?? 0);
                $velocity = round($sold / 30, 2);
                $doir = $velocity > 0 ? round($p->stock_quantity / $velocity, 1) : ($p->stock_quantity > 0 ? 999 : 0);

                fputcsv($handle, [
                    $p->sku,
                    $p->name,
                    $p->category?->name ?? 'Uncategorized',
                    $p->stock_quantity,
                    $p->min_stock_threshold,
                    number_format($p->cost_price, 2),
                    number_format($p->selling_price, 2),
                    number_format($p->stock_quantity * $p->cost_price, 2),
                    number_format($p->stock_quantity * $p->selling_price, 2),
                    $sold,
                    $velocity,
                    $doir == 999 ? 'Over 999 Days' : $doir,
                    $p->stock_quantity <= 0 ? 'Out of Stock' : ($p->stock_quantity <= $p->min_stock_threshold ? 'Low Stock' : 'Healthy'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Helper to compute start and end dates and comparison boundaries.
     */
    private function resolveDateRanges(string $range, Request $request): array
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subDay();
                $prevEnd = $end->copy()->subDay();
                $label = 'Today';
                break;
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                $prevStart = $start->copy()->subDay();
                $prevEnd = $end->copy()->subDay();
                $label = 'Yesterday';
                break;
            case '7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subDays(7);
                $prevEnd = $start->copy()->subSecond();
                $label = 'Last 7 Days';
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subMonth();
                $prevEnd = $start->copy()->subMonth()->endOfMonth();
                $label = 'This Month';
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                $prevStart = $start->copy()->subMonth();
                $prevEnd = $start->copy()->subMonth()->endOfMonth();
                $label = 'Last Month';
                break;
            case 'custom':
                $start = $request->filled('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : $now->copy()->subDays(29)->startOfDay();
                $end = $request->filled('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : $now->copy()->endOfDay();
                $diffDays = $start->diffInDays($end) ?: 1;
                $prevStart = $start->copy()->subDays($diffDays);
                $prevEnd = $start->copy()->subSecond();
                $label = $start->format('M d') . ' - ' . $end->format('M d, Y');
                break;
            case '30_days':
            default:
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subDays(30);
                $prevEnd = $start->copy()->subSecond();
                $label = 'Last 30 Days';
                break;
        }

        return [$start, $end, $prevStart, $prevEnd, $label];
    }
}
