@extends('layouts.admin')

@section('content')
    <!-- Dashboard Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Welcome back, {{ auth()->user()->name ?? 'Admin' }}</span>
                <span class="inline-block animate-bounce text-xl">👋</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Here's what's happening with your grocery store today.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <div class="hidden sm:flex items-center gap-2 px-3.5 py-2 bg-white rounded-lg border border-slate-200 text-xs text-slate-600 shadow-xs">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span class="font-medium">{{ date('F d, Y') }}</span>
            </div>
            <x-admin.button :href="\Illuminate\Support\Facades\Route::has('admin.orders.create') ? route('admin.orders.create') : url('/admin/orders')" variant="primary" size="sm" icon="plus">
                New Order
            </x-admin.button>
        </div>
    </div>

    <!-- 1. KPI Metric Cards Grid (Full Width 4-Col Layout) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
        <x-admin.stat-card
            title="Total Orders"
            :value="$stats['total_orders']['value']"
            icon="shopping-bag"
            iconColor="emerald"
            :trend="$stats['total_orders']['trend']"
            :trendUp="$stats['total_orders']['trend_up']"
            :timeframe="$stats['total_orders']['timeframe']"
        />
        <x-admin.stat-card
            title="Total Sales"
            :value="$stats['total_sales']['value']"
            icon="dollar-sign"
            iconColor="blue"
            :trend="$stats['total_sales']['trend']"
            :trendUp="$stats['total_sales']['trend_up']"
            :timeframe="$stats['total_sales']['timeframe']"
        />
        <x-admin.stat-card
            title="New Customers"
            :value="$stats['new_customers']['value']"
            icon="users"
            iconColor="purple"
            :trend="$stats['new_customers']['trend']"
            :trendUp="$stats['new_customers']['trend_up']"
            :timeframe="$stats['new_customers']['timeframe']"
        />
        <x-admin.stat-card
            title="Low Stock Items"
            :value="$stats['low_stock_items']['value']"
            icon="alert-triangle"
            iconColor="amber"
            :badge="$stats['low_stock_items']['badge']"
            :trend="$stats['low_stock_items']['trend']"
            :trendUp="false"
            timeframe=""
        />
    </div>

    <!-- 2. Main Analytics & Sidebar Grid (Full-Width Responsive 12-Column Layout) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left 8 Columns: Sales Overview Chart & Recent Orders -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Sales Overview Chart Card -->
            <x-admin.card title="Sales Overview" subtitle="Revenue trajectory and order volume trends" icon="trending-up">
                <x-slot:actions>
                    <!-- Timeframe Pill Tabs -->
                    <div class="flex items-center p-1 bg-slate-100 rounded-lg text-xs">
                        <button type="button" data-sales-range="7days" class="px-2.5 py-1 rounded-md bg-white text-slate-900 shadow-xs font-semibold transition-all cursor-pointer">
                            7 Days
                        </button>
                        <button type="button" data-sales-range="30days" class="px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                            30 Days
                        </button>
                        <button type="button" data-sales-range="3months" class="px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                            3 Months
                        </button>
                        <button type="button" data-sales-range="year" class="px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                            This Year
                        </button>
                    </div>
                </x-slot:actions>

                <!-- Chart Container (Full Card Width) -->
                <div class="relative h-80 w-full mt-2">
                    <canvas id="salesOverviewChart"></canvas>
                </div>

                <!-- Bottom Legend / Quick Summary -->
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-600"></span>
                            <span>Sales Revenue ($)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-sky-600"></span>
                            <span>Order Count</span>
                        </div>
                    </div>
                    <div class="hidden sm:block text-slate-400">
                        Updated in real-time
                    </div>
                </div>
            </x-admin.card>

            <!-- Recent Orders Table Card -->
            <x-admin.card title="Recent Orders" subtitle="Latest grocery deliveries & customer requests" icon="clock">
                <x-slot:actions>
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.orders.index') ? route('admin.orders.index') : url('/admin/orders') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                        <span>View All Orders</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </x-slot:actions>

                <div class="overflow-x-auto -mx-5 -my-5">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                            <tr>
                                <th class="px-5 py-3">Order ID</th>
                                <th class="px-5 py-3">Customer</th>
                                <th class="px-5 py-3">Items</th>
                                <th class="px-5 py-3">Total</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-5 py-3.5 font-semibold text-slate-900 whitespace-nowrap">
                                        {{ $order['id'] }}
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $order['created_at'] }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="font-medium text-slate-900">{{ $order['customer_name'] }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $order['payment_method'] }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                        {{ $order['items_count'] }} items
                                    </td>
                                    <td class="px-5 py-3.5 font-semibold text-slate-900 whitespace-nowrap">
                                        ${{ number_format($order['total'], 2) }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @if($order['status'] === 'delivered')
                                            <x-admin.badge variant="success" :dot="true">Delivered</x-admin.badge>
                                        @elseif($order['status'] === 'processing')
                                            <x-admin.badge variant="info" :dot="true">Processing</x-admin.badge>
                                        @elseif($order['status'] === 'pending')
                                            <x-admin.badge variant="warning" :dot="true">Pending</x-admin.badge>
                                        @else
                                            <x-admin.badge variant="danger" :dot="true">Cancelled</x-admin.badge>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <x-admin.button
                                            size="xs"
                                            variant="outline"
                                            :href="\Illuminate\Support\Facades\Route::has('admin.orders.show') ? route('admin.orders.show', $order['id']) : url('/admin/orders')"
                                        >
                                            Details
                                        </x-admin.button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        </div>

        <!-- Right 4 Columns: Quick Actions, Order Status Donut, Top Categories, Promotional Card -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Quick Actions Tile Grid -->
            <x-admin.card title="Quick Actions" subtitle="Frequently used grocery shortcuts" icon="zap">
                <div class="grid grid-cols-2 gap-3">
                    <a
                        href="{{ \Illuminate\Support\Facades\Route::has('admin.products.create') ? route('admin.products.create') : url('/admin/products') }}"
                        class="p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-emerald-50/50 hover:border-emerald-200 transition-all text-left group flex flex-col justify-between"
                    >
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i data-lucide="package-plus" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-800 group-hover:text-emerald-700">Add Product</div>
                            <div class="text-[10px] text-slate-400">New grocery SKU</div>
                        </div>
                    </a>

                    <a
                        href="{{ \Illuminate\Support\Facades\Route::has('admin.orders.create') ? route('admin.orders.create') : url('/admin/orders') }}"
                        class="p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-sky-50/50 hover:border-sky-200 transition-all text-left group flex flex-col justify-between"
                    >
                        <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-800 group-hover:text-sky-700">Create Order</div>
                            <div class="text-[10px] text-slate-400">Manual checkout</div>
                        </div>
                    </a>

                    <a
                        href="{{ \Illuminate\Support\Facades\Route::has('admin.inventory.index') ? route('admin.inventory.index') : url('/admin/inventory') }}"
                        class="p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-amber-50/50 hover:border-amber-200 transition-all text-left group flex flex-col justify-between"
                    >
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i data-lucide="boxes" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-800 group-hover:text-amber-700">Manage Stock</div>
                            <div class="text-[10px] text-slate-400">Adjust & restock</div>
                        </div>
                    </a>

                    <a
                        href="{{ \Illuminate\Support\Facades\Route::has('admin.reports.index') ? route('admin.reports.index') : url('/admin/reports') }}"
                        class="p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-purple-50/50 hover:border-purple-200 transition-all text-left group flex flex-col justify-between"
                    >
                        <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-800 group-hover:text-purple-700">View Reports</div>
                            <div class="text-[10px] text-slate-400">Sales & revenues</div>
                        </div>
                    </a>
                </div>
            </x-admin.card>

            <!-- Order Status Breakdown Donut Chart -->
            <x-admin.card title="Order Status Breakdown" subtitle="Distribution across active statuses" icon="pie-chart">
                <div class="relative h-52 w-full flex items-center justify-center my-2">
                    <canvas id="orderStatusDonutChart"></canvas>
                </div>

                <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-slate-100 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50/50 border border-emerald-100/60">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span class="text-slate-700">Delivered</span>
                        </div>
                        <span class="font-semibold text-emerald-800">{{ $orderStatusBreakdown['delivered']['count'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-sky-50/50 border border-sky-100/60">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-sky-600"></span>
                            <span class="text-slate-700">Processing</span>
                        </div>
                        <span class="font-semibold text-sky-800">{{ $orderStatusBreakdown['processing']['count'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-amber-50/50 border border-amber-100/60">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                            <span class="text-slate-700">Pending</span>
                        </div>
                        <span class="font-semibold text-amber-800">{{ $orderStatusBreakdown['pending']['count'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-rose-50/50 border border-rose-100/60">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                            <span class="text-slate-700">Cancelled</span>
                        </div>
                        <span class="font-semibold text-rose-800">{{ $orderStatusBreakdown['cancelled']['count'] }}</span>
                    </div>
                </div>
            </x-admin.card>

            <!-- Top Categories -->
            <x-admin.card title="Top Categories" subtitle="Highest revenue generating grocery departments" icon="layers">
                <div class="space-y-4">
                    @foreach($topCategories as $category)
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <div class="font-medium text-slate-800 truncate">{{ $category['name'] }}</div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] text-slate-400">{{ $category['item_count'] }}</span>
                                    <span class="text-[11px] font-semibold text-emerald-600">{{ $category['growth'] }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $category['share_percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>

            <!-- Brand-Neutral Grocery Promotional Card -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-800 p-5 text-white shadow-lg shadow-emerald-900/20">
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="relative z-10 space-y-3">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 text-white text-[11px] font-medium backdrop-blur-xs">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Campaign Active</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold tracking-tight">Fresh Produce Seasonal Boost</h3>
                        <p class="text-xs text-emerald-100 mt-1">
                            Fresh fruit sales increased by 24% this week. Schedule restock to maintain inventory.
                        </p>
                    </div>
                    <div class="pt-1">
                        <a
                            href="{{ \Illuminate\Support\Facades\Route::has('admin.inventory.index') ? route('admin.inventory.index') : url('/admin/inventory') }}"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white text-emerald-800 text-xs font-semibold hover:bg-emerald-50 transition-colors shadow-sm"
                        >
                            <span>Inspect Inventory</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    window.salesChartDataset = @json($salesChartData);
    window.orderStatusDataset = @json($orderStatusBreakdown);

    function triggerDashboardCharts() {
        if (window.Admin && typeof window.Admin.initDashboard === 'function') {
            window.Admin.initDashboard(window.salesChartDataset, window.orderStatusDataset);
        }
        if (window.Admin && typeof window.Admin.refreshIcons === 'function') {
            window.Admin.refreshIcons();
        }
    }

    triggerDashboardCharts();
    document.addEventListener('DOMContentLoaded', triggerDashboardCharts);
    window.addEventListener('load', triggerDashboardCharts);
</script>
@endpush
