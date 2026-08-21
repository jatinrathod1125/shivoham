@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Inventory Audit History"
        subtitle="Immutable ledger of all warehouse intakes, order deductions, spoilage write-offs, and stock adjustments."
        :breadcrumbs="[
            ['title' => 'Inventory', 'url' => route('admin.inventory.index')],
            ['title' => 'Audit History', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.inventory.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Stock Overview
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- KPI Metric Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                <i data-lucide="history" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Transactions</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($historyStats['total']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Restock Intakes</p>
                <p class="text-xl font-bold text-emerald-600 mt-0.5">{{ number_format($historyStats['additions']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Order Deductions</p>
                <p class="text-xl font-bold text-blue-600 mt-0.5">{{ number_format($historyStats['orders']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="sliders" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Manual Corrections</p>
                <p class="text-xl font-bold text-purple-600 mt-0.5">{{ number_format($historyStats['adjustments']) }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.inventory.history') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
            <!-- Search Input -->
            <div class="lg:col-span-6">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search product name, SKU, PO/order reference, reason memo..."
                        class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                    />
                </div>
            </div>

            <!-- Transaction Type Filter -->
            <div class="lg:col-span-3">
                <select
                    name="type"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Movement Types</option>
                    <option value="addition" {{ request('type') === 'addition' ? 'selected' : '' }}>Restock / Intake (+)</option>
                    <option value="order" {{ request('type') === 'order' ? 'selected' : '' }}>Customer Order Sale (-)</option>
                    <option value="deduction" {{ request('type') === 'deduction' ? 'selected' : '' }}>Damage / Spoilage (-)</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Manual Inventory Correction</option>
                    <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Customer Return (+)</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <x-admin.button type="submit" variant="secondary" size="sm" class="w-full sm:w-auto">
                    Filter Ledger
                </x-admin.button>

                @if(request()->hasAny(['search', 'type', 'product_id']))
                    <a
                        href="{{ route('admin.inventory.history') }}"
                        class="p-2 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Clear filters"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.card>

    <!-- Audit Ledger Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Timestamp</th>
                        <th class="px-5 py-3">Product Item</th>
                        <th class="px-5 py-3">Movement Type</th>
                        <th class="px-5 py-3 text-center">Quantity Delta</th>
                        <th class="px-5 py-3 text-center">Stock Flow</th>
                        <th class="px-5 py-3">Recorded By</th>
                        <th class="px-5 py-3">Reason / Ref Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Timestamp -->
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500">
                                <div class="font-medium text-slate-800">{{ $tx->created_at->format('M d, Y') }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $tx->created_at->format('h:i:s A') }}</div>
                            </td>

                            <!-- Product Info -->
                            <td class="px-5 py-3.5">
                                @if($tx->product)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200/80 text-slate-500 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                                            @if($tx->product->thumbnail)
                                                <img src="{{ $tx->product->thumbnail }}" alt="{{ $tx->product->name }}" class="w-full h-full object-cover" />
                                            @else
                                                <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.products.edit', $tx->product) }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors truncate block max-w-[180px]">
                                                {{ $tx->product->name }}
                                            </a>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $tx->product->sku }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Deleted Product (#{{ $tx->product_id }})</span>
                                @endif
                            </td>

                            <!-- Movement Type Badge -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @switch($tx->type)
                                    @case('addition')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-semibold border border-emerald-200">
                                            <i data-lucide="arrow-down-left" class="w-3 h-3 text-emerald-600"></i>
                                            <span>Restock Intake</span>
                                        </span>
                                        @break
                                    @case('order')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 text-[11px] font-semibold border border-blue-200">
                                            <i data-lucide="shopping-bag" class="w-3 h-3 text-blue-600"></i>
                                            <span>Order Sale</span>
                                        </span>
                                        @break
                                    @case('deduction')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-800 text-[11px] font-semibold border border-rose-200">
                                            <i data-lucide="trash" class="w-3 h-3 text-rose-600"></i>
                                            <span>Damage / Spoilage</span>
                                        </span>
                                        @break
                                    @case('adjustment')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-800 text-[11px] font-semibold border border-purple-200">
                                            <i data-lucide="sliders" class="w-3 h-3 text-purple-600"></i>
                                            <span>Count Correction</span>
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px] font-medium">
                                            {{ ucfirst($tx->type) }}
                                        </span>
                                @endswitch
                            </td>

                            <!-- Quantity Delta -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <span class="font-mono font-bold text-xs {{ in_array($tx->type, ['addition', 'return']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ in_array($tx->type, ['addition', 'return']) ? '+' : '-' }}{{ $tx->quantity }}
                                </span>
                            </td>

                            <!-- Stock Flow -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap font-mono text-xs">
                                <span class="text-slate-400">{{ $tx->previous_stock }}</span>
                                <span class="text-slate-300 mx-1">➔</span>
                                <span class="font-bold text-slate-900">{{ $tx->current_stock }}</span>
                            </td>

                            <!-- Recorded By -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($tx->user)
                                    <div class="flex items-center gap-1.5 text-xs text-slate-800 font-medium">
                                        <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold flex items-center justify-center">
                                            {{ substr($tx->user->name, 0, 1) }}
                                        </div>
                                        <span>{{ $tx->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">System (Customer Checkout)</span>
                                @endif
                            </td>

                            <!-- Reason & Reference -->
                            <td class="px-5 py-3.5">
                                <div class="text-xs text-slate-800 truncate max-w-[240px]">{{ $tx->reason }}</div>
                                @if($tx->reference_id)
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $tx->reference_id }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <x-admin.empty-state
                                    title="No Audit Records Found"
                                    description="No stock movements match your query filters."
                                    icon="history"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </x-admin.card>
@endsection
