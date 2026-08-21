@extends('layouts.admin')

@section('content')
    <!-- Page Header & Action Bar -->
    <x-admin.page-header
        title="Analytics & Sales Reports"
        subtitle="Business performance metrics, daily revenue trends, category distributions, and top selling products."
        :breadcrumbs="[
            ['title' => 'Analytics', 'url' => ''],
            ['title' => 'Sales Performance', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.reports.export', request()->all()) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200/80 text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-1.5 shadow-2xs"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Export CSV</span>
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer"
                >
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print Report</span>
                </button>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- Analytics Mode Navigation Switcher -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-1">
        <a
            href="{{ route('admin.reports.index') }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-emerald-600 text-white shadow-xs"
        >
            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
            <span>Sales & Revenue Analytics</span>
        </a>

        <a
            href="{{ route('admin.reports.inventory') }}"
            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/80 transition-all flex items-center gap-2"
        >
            <i data-lucide="boxes" class="w-4 h-4 text-slate-400"></i>
            <span>Inventory Velocity & Forecasting</span>
        </a>
    </div>

    <!-- Date Range Filter Switcher -->
    <div class="p-3 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <!-- Preset Range Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto">
            <a
                href="{{ route('admin.reports.index', ['range' => 'today']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $range === 'today' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                Today
            </a>
            <a
                href="{{ route('admin.reports.index', ['range' => 'yesterday']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $range === 'yesterday' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                Yesterday
            </a>
            <a
                href="{{ route('admin.reports.index', ['range' => '7_days']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $range === '7_days' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                Last 7 Days
            </a>
            <a
                href="{{ route('admin.reports.index', ['range' => '30_days']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $range === '30_days' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                Last 30 Days
            </a>
            <a
                href="{{ route('admin.reports.index', ['range' => 'this_month']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $range === 'this_month' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                This Month
            </a>
            <a
                href="{{ route('admin.reports.index', ['range' => 'last_month']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $range === 'last_month' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                Last Month
            </a>
        </div>

        <!-- Custom Date Range Form -->
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="range" value="custom" />
            <input
                type="date"
                name="start_date"
                value="{{ $startDate->format('Y-m-d') }}"
                class="px-2.5 py-1 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:bg-white focus:outline-hidden focus:border-emerald-500"
            />
            <span class="text-xs text-slate-400">to</span>
            <input
                type="date"
                name="end_date"
                value="{{ $endDate->format('Y-m-d') }}"
                class="px-2.5 py-1 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:bg-white focus:outline-hidden focus:border-emerald-500"
            />
            <button
                type="submit"
                class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer"
            >
                Apply
            </button>
        </form>
    </div>

    <!-- Active Range Banner Indicator -->
    <div class="flex items-center gap-2 text-xs text-slate-500">
        <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600"></i>
        <span>Viewing data from <strong>{{ $startDate->format('M d, Y') }}</strong> to <strong>{{ $endDate->format('M d, Y') }}</strong> ({{ $rangeLabel }})</span>
    </div>

    <!-- KPI Performance Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Gross Revenue -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Gross Revenue</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">${{ number_format($kpis['gross_revenue'], 2) }}</p>
                <div class="flex items-center gap-1.5 mt-1 text-xs">
                    @if($kpis['revenue_growth'] >= 0)
                        <span class="text-emerald-600 font-bold flex items-center">
                            <i data-lucide="trending-up" class="w-3.5 h-3.5 mr-0.5"></i> +{{ $kpis['revenue_growth'] }}%
                        </span>
                    @else
                        <span class="text-rose-500 font-bold flex items-center">
                            <i data-lucide="trending-down" class="w-3.5 h-3.5 mr-0.5"></i> {{ $kpis['revenue_growth'] }}%
                        </span>
                    @endif
                    <span class="text-slate-400">vs prior period</span>
                </div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Completed Orders</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($kpis['completed_orders']) }}</p>
                <div class="flex items-center gap-1.5 mt-1 text-xs">
                    @if($kpis['order_growth'] >= 0)
                        <span class="text-emerald-600 font-bold flex items-center">
                            <i data-lucide="trending-up" class="w-3.5 h-3.5 mr-0.5"></i> +{{ $kpis['order_growth'] }}%
                        </span>
                    @else
                        <span class="text-rose-500 font-bold flex items-center">
                            <i data-lucide="trending-down" class="w-3.5 h-3.5 mr-0.5"></i> {{ $kpis['order_growth'] }}%
                        </span>
                    @endif
                    <span class="text-slate-400">({{ $kpis['total_orders'] }} total placed)</span>
                </div>
            </div>
        </div>

        <!-- Average Order Value (AOV) -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Average Order Value</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">${{ number_format($kpis['aov'], 2) }}</p>
                <div class="text-xs text-slate-500 mt-1">
                    <span>Avg. <strong>{{ $kpis['avg_basket'] }}</strong> items per basket</span>
                </div>
            </div>
        </div>

        <!-- Gross Profit & Margin -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Est. Gross Profit</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">${{ number_format($kpis['gross_profit'], 2) }}</p>
                <div class="text-xs text-slate-500 mt-1">
                    <span class="font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">{{ $kpis['profit_margin'] }}%</span>
                    <span class="text-slate-400 ml-1">gross margin</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Charts Grid (Daily Sales & Category Distribution) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Main Timeline Chart: Daily Sales & Orders -->
        <div class="lg:col-span-8">
            <x-admin.card title="Revenue & Orders Timeline" subtitle="Daily revenue trend and order volume over selected interval" icon="activity">
                <div id="revenue-timeline-chart" class="min-h-[320px] w-full"></div>
            </x-admin.card>
        </div>

        <!-- Donut Chart: Category Revenue Breakdown -->
        <div class="lg:col-span-4">
            <x-admin.card title="Category Sales Share" subtitle="Revenue split by grocery category" icon="pie-chart">
                <div id="category-donut-chart" class="min-h-[320px] flex items-center justify-center"></div>
            </x-admin.card>
        </div>
    </div>

    <!-- Structured Performance Data Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Top 10 Best Selling Grocery Products -->
        <div class="lg:col-span-7">
            <x-admin.card title="Top 10 Best-Selling Products" subtitle="Most purchased grocery items by total revenue" icon="award">
                <div class="overflow-x-auto -mx-5 -my-5">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                            <tr>
                                <th class="px-5 py-3">Product</th>
                                <th class="px-5 py-3 text-center">Units Sold</th>
                                <th class="px-5 py-3 text-right">Revenue ($)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($topProducts as $index => $item)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="w-5 text-center text-xs font-bold {{ $index < 3 ? 'text-amber-500' : 'text-slate-400' }}">
                                                #{{ $index + 1 }}
                                            </span>
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-900 truncate max-w-[220px]">{{ $item->product_name }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono">{{ $item->sku }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center whitespace-nowrap font-bold text-slate-800">
                                        {{ number_format($item->total_qty) }}
                                    </td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap font-bold text-emerald-600">
                                        ${{ number_format($item->total_sales, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400">
                                        No sales records found in this date range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        </div>

        <!-- Top VIP Customer Spenders -->
        <div class="lg:col-span-5">
            <x-admin.card title="Top VIP Customers" subtitle="Highest lifetime spenders" icon="crown">
                <div class="overflow-x-auto -mx-5 -my-5">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                            <tr>
                                <th class="px-5 py-3">Customer</th>
                                <th class="px-5 py-3 text-center">Orders</th>
                                <th class="px-5 py-3 text-right">Lifetime ($)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($topCustomers as $customer)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="font-bold text-slate-900 truncate max-w-[160px]">{{ $customer->name }}</div>
                                        <div class="text-[11px] text-slate-400 truncate max-w-[160px]">{{ $customer->email }}</div>
                                    </td>
                                    <td class="px-5 py-3 text-center whitespace-nowrap text-slate-600 font-semibold">
                                        {{ $customer->orders_count ?? 0 }}
                                    </td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap font-bold text-slate-900">
                                        ${{ number_format($customer->total_spent, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400">
                                        No customer records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    $(function () {
        const chartDates = {!! $chartDates !!};
        const chartRevenues = {!! $chartRevenues !!};
        const chartOrders = {!! $chartOrders !!};

        // 1. Daily Revenue Timeline Chart
        const timelineOptions = {
            series: [
                {
                    name: 'Revenue ($)',
                    type: 'area',
                    data: chartRevenues
                },
                {
                    name: 'Orders',
                    type: 'line',
                    data: chartOrders
                }
            ],
            chart: {
                height: 330,
                type: 'line',
                toolbar: { show: false },
                fontFamily: 'Instrument Sans, sans-serif'
            },
            colors: ['#059669', '#3b82f6'],
            stroke: {
                curve: 'smooth',
                width: [2.5, 2]
            },
            fill: {
                type: ['gradient', 'solid'],
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: chartDates,
                labels: {
                    style: { fontSize: '11px', colors: '#64748b' },
                    rotate: -45,
                    rotateAlways: false
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: [
                {
                    title: { text: 'Revenue ($)', style: { fontSize: '11px', color: '#059669' } },
                    labels: {
                        formatter: val => '$' + val.toFixed(0),
                        style: { fontSize: '11px', colors: '#64748b' }
                    }
                },
                {
                    opposite: true,
                    title: { text: 'Orders', style: { fontSize: '11px', color: '#3b82f6' } },
                    labels: {
                        formatter: val => val.toFixed(0),
                        style: { fontSize: '11px', colors: '#64748b' }
                    }
                }
            ],
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function(val, { seriesIndex }) {
                        return seriesIndex === 0 ? '$' + val.toFixed(2) : val + ' orders';
                    }
                }
            }
        };

        const timelineChart = new ApexCharts($('#revenue-timeline-chart')[0], timelineOptions);
        timelineChart.render();

        // 2. Category Revenue Breakdown Donut Chart
        const catLabels = {!! $catLabels !!};
        const catRevenues = {!! $catRevenues !!};

        if (catRevenues.length > 0) {
            const donutOptions = {
                series: catRevenues,
                labels: catLabels,
                chart: {
                    type: 'donut',
                    height: 310,
                    fontFamily: 'Instrument Sans, sans-serif'
                },
                colors: ['#059669', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4', '#64748b'],
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    markers: { radius: 12 }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Revenue',
                                    formatter: w => '$' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toFixed(0)
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                tooltip: {
                    y: {
                        formatter: val => '$' + Number(val).toFixed(2)
                    }
                }
            };

            const donutChart = new ApexCharts($('#category-donut-chart')[0], donutOptions);
            donutChart.render();
        } else {
            $('#category-donut-chart').html(`
                <div class="text-center py-12 text-slate-400 text-xs">
                    <p>No category revenue data in this period</p>
                </div>
            `);
        }
    });
</script>
@endpush
