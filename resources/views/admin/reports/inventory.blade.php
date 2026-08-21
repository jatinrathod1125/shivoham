@extends('layouts.admin')

@section('content')
    <!-- Page Header & Action Bar -->
    <x-admin.page-header
        title="Inventory Velocity & Stock Health"
        subtitle="Stock run rates, days of inventory remaining (DOIR), dead stock analysis, and reorder forecasting."
        :breadcrumbs="[
            ['title' => 'Analytics', 'url' => ''],
            ['title' => 'Inventory Velocity', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.reports.inventory.export') }}"
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
                    <span>Print Health Report</span>
                </button>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- Analytics Mode Navigation Switcher -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-1">
        <a
            href="{{ route('admin.reports.index') }}"
            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/80 transition-all flex items-center gap-2"
        >
            <i data-lucide="bar-chart-3" class="w-4 h-4 text-slate-400"></i>
            <span>Sales & Revenue Analytics</span>
        </a>

        <a
            href="{{ route('admin.reports.inventory') }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-emerald-600 text-white shadow-xs"
        >
            <i data-lucide="boxes" class="w-4 h-4"></i>
            <span>Inventory Velocity & Forecasting</span>
        </a>
    </div>

    <!-- Stock Health KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Catalog Retail Valuation -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Catalog Valuation</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="badge-dollar-sign" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">${{ number_format($kpis['retail_value'], 2) }}</p>
                <div class="text-xs text-slate-400 mt-1">
                    <span>Wholesale Cost: <strong>${{ number_format($kpis['cost_value'], 2) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Total Stock Units & SKUs -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Inventory Units</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="boxes" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($kpis['total_units']) }}</p>
                <div class="text-xs text-slate-400 mt-1">
                    <span>Across <strong>{{ number_format($kpis['total_skus']) }}</strong> Active SKUs</span>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Low Stock Warnings</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-amber-600 tracking-tight">{{ number_format($kpis['low_stock']) }}</p>
                <div class="text-xs text-amber-700 mt-1 font-semibold">
                    <span>At or below reorder threshold</span>
                </div>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Out of Stock</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-rose-600 tracking-tight">{{ number_format($kpis['out_of_stock']) }}</p>
                <div class="text-xs text-rose-600 mt-1 font-semibold">
                    <span>Zero available units</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Stock Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Stock Health Donut -->
        <div class="lg:col-span-4">
            <x-admin.card title="Stock Health Breakdown" subtitle="Distribution of catalog SKU stock status" icon="pie-chart">
                <div id="stock-health-donut" class="min-h-[300px] flex items-center justify-center"></div>
            </x-admin.card>
        </div>

        <!-- Category Value Distribution Bar -->
        <div class="lg:col-span-8">
            <x-admin.card title="Inventory Valuation by Category" subtitle="Total inventory dollars tied up per department" icon="bar-chart-2">
                <div id="category-value-bar" class="min-h-[300px]"></div>
            </x-admin.card>
        </div>
    </div>

    <!-- Fast Moving Inventory & Reorder Forecasting Matrix -->
    <x-admin.card title="Fast-Moving Items & Reorder Velocity Forecasting" subtitle="Run rates, units/day velocity, and estimated days of inventory remaining (DOIR)" icon="trending-up">
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Product Item</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3 text-center">Stock on Hand</th>
                        <th class="px-5 py-3 text-center">30-Day Sales</th>
                        <th class="px-5 py-3 text-center">Run Rate (Daily)</th>
                        <th class="px-5 py-3 text-center">Days Remaining (DOIR)</th>
                        <th class="px-5 py-3 text-center">Urgency</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($fastMoving as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 truncate max-w-[200px]">{{ $p->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $p->sku }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-600">
                                {{ $p->category?->name ?? 'Uncategorized' }}
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap font-bold text-slate-900">
                                {{ number_format($p->stock_quantity) }}
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap font-bold text-emerald-600">
                                {{ number_format($p->units_sold_30d) }} sold
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap text-slate-600">
                                {{ $p->daily_velocity }} / day
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap font-mono font-bold text-slate-800">
                                @if($p->doir <= 7)
                                    <span class="text-rose-600 font-extrabold">{{ $p->doir }} days</span>
                                @elseif($p->doir <= 14)
                                    <span class="text-amber-600 font-bold">{{ $p->doir }} days</span>
                                @else
                                    <span class="text-emerald-600">{{ $p->doir }} days</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                @if($p->urgency_color === 'rose')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $p->urgency }}
                                    </span>
                                @elseif($p->urgency_color === 'amber')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ $p->urgency }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $p->urgency }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a
                                    href="{{ route('admin.inventory.index', ['search' => $p->sku]) }}"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors inline-flex items-center gap-1"
                                >
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                    <span>Restock</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                No 30-day velocity records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <!-- Stagnant / Slow Moving Capital Warning -->
    <x-admin.card title="Slow-Moving & Dead Stock Analysis" subtitle="High stock items with zero sales in past 30 days and tied-up working capital" icon="alert-circle">
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Product Item</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3 text-center">Unsold Units on Hand</th>
                        <th class="px-5 py-3 text-right">Tied-Up Working Capital ($)</th>
                        <th class="px-5 py-3 text-right">Suggested Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($slowMoving as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 truncate max-w-[220px]">{{ $p->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $p->sku }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-600">
                                {{ $p->category?->name ?? 'Uncategorized' }}
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap font-bold text-slate-900">
                                {{ number_format($p->stock_quantity) }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap font-bold text-rose-600">
                                ${{ number_format($p->tied_up_capital, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a
                                    href="{{ route('admin.offers.create') }}"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 transition-colors inline-flex items-center gap-1"
                                >
                                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                    <span>Create Promo Deal</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                No stagnant inventory identified.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    $(function () {
        // 1. Stock Health Donut
        const healthSeries = {!! $stockHealthSeries !!};
        const healthDonutOptions = {
            series: healthSeries,
            labels: ['Healthy Stock', 'Low Stock Warning', 'Out of Stock'],
            chart: {
                type: 'donut',
                height: 290,
                fontFamily: 'Instrument Sans, sans-serif'
            },
            colors: ['#059669', '#f59e0b', '#e11d48'],
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
                                label: 'Total SKUs',
                                formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false }
        };

        const healthDonut = new ApexCharts($('#stock-health-donut')[0], healthDonutOptions);
        healthDonut.render();

        // 2. Category Inventory Valuation Bar Chart
        const catNames = {!! $catNames !!};
        const catValues = {!! $catValues !!};

        const catBarOptions = {
            series: [{
                name: 'Inventory Value ($)',
                data: catValues
            }],
            chart: {
                type: 'bar',
                height: 290,
                toolbar: { show: false },
                fontFamily: 'Instrument Sans, sans-serif'
            },
            colors: ['#059669'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '45%',
                    distributed: false
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: catNames,
                labels: {
                    style: { fontSize: '11px', colors: '#64748b' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: val => '$' + Number(val).toFixed(0),
                    style: { fontSize: '11px', colors: '#64748b' }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            },
            tooltip: {
                y: {
                    formatter: val => '$' + Number(val).toFixed(2)
                }
            }
        };

        const catBar = new ApexCharts($('#category-value-bar')[0], catBarOptions);
        catBar.render();
    });
</script>
@endpush
