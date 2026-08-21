@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Inventory Management"
        subtitle="Track grocery stock levels, restock alerts, inventory valuation, and warehouse adjustments."
        :breadcrumbs="[
            ['title' => 'Inventory', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.inventory.history')"
                variant="outline"
                size="sm"
                icon="history"
            >
                Audit Ledger History
            </x-admin.button>

            <x-admin.button
                :href="route('admin.products.create')"
                variant="primary"
                size="sm"
                icon="plus"
            >
                Add New Product
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- KPI Metric Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="boxes" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total SKUs</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total_skus']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Low Stock Alerts</p>
                <p class="text-xl font-bold text-amber-600 mt-0.5">{{ number_format($stats['low_stock']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Out of Stock</p>
                <p class="text-xl font-bold text-rose-600 mt-0.5">{{ number_format($stats['out_of_stock']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <i data-lucide="dollar-sign" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Valuation</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">${{ number_format($stats['total_value'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Priority Low Stock Restock Banner -->
    @if($priorityLowStock->isNotEmpty())
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
                    <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Priority Restock Alerts (Critical Threshold)</h3>
                </div>
                <span class="text-xs font-medium text-amber-700">{{ $stats['low_stock'] }} items below safety margin</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($priorityLowStock as $critItem)
                    <div class="p-3 rounded-xl bg-white border border-amber-200/80 shadow-2xs flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ $critItem->name }}</p>
                            <div class="flex items-center gap-1.5 text-[11px] text-amber-700 font-semibold mt-0.5">
                                <span>{{ $critItem->stock_quantity }} {{ $critItem->unit }} remaining</span>
                                <span class="text-slate-400 font-normal">(Min: {{ $critItem->min_stock_threshold }})</span>
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="openQuickStockModal({{ $critItem->id }}, '{{ addslashes($critItem->name) }}', {{ $critItem->stock_quantity }}, '{{ $critItem->unit }}')"
                            class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold border border-amber-200 shrink-0 transition-colors cursor-pointer"
                        >
                            Restock
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filter & Search Toolbar -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
            <!-- Search Input -->
            <div class="lg:col-span-5">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search product name, SKU, barcode..."
                        class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                    />
                </div>
            </div>

            <!-- Category Filter -->
            <div class="lg:col-span-3">
                <select
                    name="category_id"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Categories</option>
                    @foreach($categories as $parentCat)
                        <option value="{{ $parentCat->id }}" {{ request('category_id') == $parentCat->id ? 'selected' : '' }}>
                            {{ $parentCat->name }}
                        </option>
                        @foreach($parentCat->children as $childCat)
                            <option value="{{ $childCat->id }}" {{ request('category_id') == $childCat->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;↳ {{ $childCat->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <!-- Stock Status Filter -->
            <div class="lg:col-span-2">
                <select
                    name="stock_status"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Stock Levels</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>

            <!-- Sort Filter -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                >
                    <option value="stock_asc" {{ request('sort') === 'stock_asc' ? 'selected' : '' }}>Stock: Low to High</option>
                    <option value="stock_desc" {{ request('sort') === 'stock_desc' ? 'selected' : '' }}>Stock: High to Low</option>
                    <option value="value_desc" {{ request('sort') === 'value_desc' ? 'selected' : '' }}>Value: High to Low</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                </select>

                @if(request()->hasAny(['search', 'category_id', 'stock_status', 'sort']))
                    <a
                        href="{{ route('admin.inventory.index') }}"
                        class="p-2 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Clear filters"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.card>

    <!-- Inventory Stock Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Current Stock</th>
                        <th class="px-5 py-3">Threshold Limit</th>
                        <th class="px-5 py-3">Inventory Valuation</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Stock Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/70 transition-colors" id="inventory-row-{{ $product->id }}">
                            <!-- Product Details -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 text-slate-500 flex items-center justify-center shrink-0 overflow-hidden shadow-xs">
                                        @if($product->thumbnail)
                                            <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                                        @else
                                            <i data-lucide="package" class="w-5 h-5 text-slate-400"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors truncate block max-w-[200px]">
                                            {{ $product->name }}
                                        </a>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($product->category)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium">
                                        <span>{{ $product->category->name }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">Uncategorized</span>
                                @endif
                            </td>

                            <!-- Current Stock -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-sm text-slate-900">
                                    {{ $product->stock_quantity }} <span class="text-xs font-normal text-slate-500">{{ $product->unit }}</span>
                                </div>
                            </td>

                            <!-- Min Stock Threshold -->
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 font-mono">
                                {{ $product->min_stock_threshold }} {{ $product->unit }}
                            </td>

                            <!-- Inventory Valuation -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="text-xs font-bold text-slate-900">
                                    ${{ number_format($product->stock_quantity * $product->cost_price, 2) }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    @ ${{ number_format($product->cost_price, 2) }} / unit
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($product->stock_quantity <= 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 text-[11px] font-bold border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Out of Stock</span>
                                    </span>
                                @elseif($product->is_low_stock)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 text-[11px] font-bold border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Low Stock</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-semibold border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>In Stock</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Stock Action -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    onclick="openQuickStockModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->stock_quantity }}, '{{ $product->unit }}')"
                                    class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-200 transition-colors inline-flex items-center gap-1.5 cursor-pointer"
                                >
                                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                    <span>Adjust Stock</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <x-admin.empty-state
                                    title="No Inventory Items Found"
                                    description="No products match your inventory filter criteria."
                                    icon="boxes"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </x-admin.card>

    <!-- Quick Stock Adjustment Modal -->
    <div id="quick-stock-modal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Inventory Adjustment</h3>
                        <p class="text-[11px] text-slate-500 truncate max-w-[280px]" id="modal-product-name">Product Name</p>
                    </div>
                </div>
                <button type="button" onclick="closeQuickStockModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form id="quick-stock-form" onsubmit="handleInventoryAdjustmentSubmit(event)" class="p-5 space-y-4">
                <input type="hidden" id="modal-product-id" name="product_id" value="" />

                <!-- Action Type -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Adjustment Action</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="border border-slate-200 rounded-xl p-2.5 text-center cursor-pointer hover:bg-slate-50 has-checked:border-emerald-500 has-checked:bg-emerald-50/50 has-checked:text-emerald-800 transition-colors">
                            <input type="radio" name="type" value="addition" checked class="sr-only" />
                            <span class="block text-xs font-bold text-emerald-700">Restock (+)</span>
                        </label>
                        <label class="border border-slate-200 rounded-xl p-2.5 text-center cursor-pointer hover:bg-slate-50 has-checked:border-emerald-500 has-checked:bg-emerald-50/50 has-checked:text-emerald-800 transition-colors">
                            <input type="radio" name="type" value="deduction" class="sr-only" />
                            <span class="block text-xs font-bold text-rose-700">Damage (-)</span>
                        </label>
                        <label class="border border-slate-200 rounded-xl p-2.5 text-center cursor-pointer hover:bg-slate-50 has-checked:border-emerald-500 has-checked:bg-emerald-50/50 has-checked:text-emerald-800 transition-colors">
                            <input type="radio" name="type" value="adjustment" class="sr-only" />
                            <span class="block text-xs font-bold text-slate-700">Set Exact</span>
                        </label>
                    </div>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Quantity (<span id="modal-product-unit">units</span>)</label>
                    <input
                        type="number"
                        id="modal-quantity-input"
                        name="quantity"
                        min="0"
                        required
                        class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden transition-all"
                    />
                </div>

                <!-- Reason / Audit Note -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Reason / Batch Memo</label>
                    <input
                        type="text"
                        id="modal-reason-input"
                        name="reason"
                        placeholder="e.g. Fresh farm delivery, Shelf spoilage write-off"
                        class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden transition-all"
                    />
                </div>

                <!-- Reference ID -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">PO / Batch Reference (Optional)</label>
                    <input
                        type="text"
                        id="modal-reference-input"
                        name="reference_id"
                        placeholder="e.g. PO-84920"
                        class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden transition-all font-mono"
                    />
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <x-admin.button type="button" variant="outline" size="sm" onclick="closeQuickStockModal()">
                        Cancel
                    </x-admin.button>
                    <x-admin.button type="submit" variant="primary" size="sm" icon="check">
                        Record Adjustment
                    </x-admin.button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openQuickStockModal(id, name, currentStock, unit) {
        $('#modal-product-id').val(id);
        $('#modal-product-name').text(name);
        $('#modal-product-unit').text(unit || 'units');
        $('#modal-quantity-input').val(10);
        $('#modal-reason-input').val('');
        $('#modal-reference-input').val('');
        $('#quick-stock-modal').removeClass('hidden');
    }

    function closeQuickStockModal() {
        $('#quick-stock-modal').addClass('hidden');
    }

    function handleInventoryAdjustmentSubmit(e) {
        e.preventDefault();
        const formData = new FormData($('#quick-stock-form')[0]);

        $.ajax({
            url: "{{ route('admin.inventory.adjust') }}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    closeQuickStockModal();
                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'success', title: 'Inventory Updated', message: data.message });
                    }
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to record adjustment.' });
                    }
                }
            },
            error: function() {
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'error', title: 'Server Error', message: 'Could not connect to server.' });
                }
            }
        });
    }
</script>
@endpush
