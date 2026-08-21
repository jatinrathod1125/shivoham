@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Products"
        subtitle="Manage grocery catalog SKUs, inventory levels, pricing, and classifications."
        :breadcrumbs="[
            ['title' => 'Products', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.products.create')"
                variant="primary"
                size="sm"
                icon="plus"
            >
                Add Product
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- KPI Metric Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Products</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</p>
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
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Catalog Valuation</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">${{ number_format($stats['total_value'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Advanced Multi-Facet Filter Toolbar -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.products.index') }}" class="space-y-3.5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                <!-- Search Input -->
                <div class="lg:col-span-4">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search name, SKU, barcode..."
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
                                {{ $parentCat->name }} (Main)
                            </option>
                            @foreach($parentCat->children as $childCat)
                                <option value="{{ $childCat->id }}" {{ request('category_id') == $childCat->id ? 'selected' : '' }}>
                                    &nbsp;&nbsp;↳ {{ $childCat->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <!-- Brand Filter -->
                <div class="lg:col-span-3">
                    <select
                        name="brand_id"
                        onchange="this.form.submit()"
                        class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                    >
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
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
            </div>

            <!-- Second Row Filters & Sorters -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div class="flex items-center gap-3">
                    <!-- Status Filter -->
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="py-1.5 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                    >
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive Only</option>
                    </select>

                    <!-- Featured Filter -->
                    <select
                        name="featured"
                        onchange="this.form.submit()"
                        class="py-1.5 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                    >
                        <option value="">Featured: All</option>
                        <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured Only</option>
                    </select>

                    <!-- On Sale Filter -->
                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                        <input
                            type="checkbox"
                            name="on_sale"
                            value="1"
                            {{ request('on_sale') === '1' ? 'checked' : '' }}
                            onchange="this.form.submit()"
                            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        <span>On Sale Only</span>
                    </label>
                </div>

                <!-- Sort Order & Clear -->
                <div class="flex items-center gap-2">
                    <select
                        name="sort"
                        onchange="this.form.submit()"
                        class="py-1.5 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                    >
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="stock_low" {{ request('sort') === 'stock_low' ? 'selected' : '' }}>Stock: Low to High</option>
                    </select>

                    @if(request()->hasAny(['search', 'category_id', 'brand_id', 'stock_status', 'status', 'featured', 'on_sale', 'sort']))
                        <a
                            href="{{ route('admin.products.index') }}"
                            class="px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50 rounded-lg transition-colors font-medium flex items-center gap-1"
                        >
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Clear</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </x-admin.card>

    <!-- Products Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Category & Brand</th>
                        <th class="px-5 py-3">Pricing & Margin</th>
                        <th class="px-5 py-3">Stock Level</th>
                        <th class="px-5 py-3 text-center">Featured</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/70 transition-colors" id="product-row-{{ $product->id }}">
                            <!-- Product Info -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200/80 text-slate-500 flex items-center justify-center shrink-0 overflow-hidden shadow-xs">
                                        @if($product->thumbnail)
                                            <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                                        @else
                                            <i data-lucide="package" class="w-6 h-6 text-slate-400"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors truncate max-w-[220px]">
                                                {{ $product->name }}
                                            </a>
                                            @if($product->is_on_sale)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200/60 uppercase tracking-tight">
                                                    Sale
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                            <span class="font-mono">{{ $product->sku }}</span>
                                            @if($product->barcode)
                                                <span>•</span>
                                                <span class="font-mono">{{ $product->barcode }}</span>
                                            @endif
                                            @if($product->unit)
                                                <span>•</span>
                                                <span>{{ $product->unit }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Category & Brand -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="space-y-1">
                                    @if($product->category)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[11px] font-medium border border-emerald-100">
                                            <i data-lucide="folder" class="w-3 h-3 text-emerald-500"></i>
                                            <span>{{ $product->category->name }}</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">Uncategorized</span>
                                    @endif

                                    @if($product->brand)
                                        <div class="text-[11px] text-slate-500 font-medium flex items-center gap-1">
                                            <i data-lucide="tag" class="w-3 h-3 text-slate-400"></i>
                                            <span>{{ $product->brand->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Pricing -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="space-y-0.5">
                                    @if($product->is_on_sale)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-bold text-rose-600">${{ number_format($product->special_price, 2) }}</span>
                                            <span class="text-[11px] text-slate-400 line-through">${{ number_format($product->selling_price, 2) }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs font-bold text-slate-900">${{ number_format($product->selling_price, 2) }}</span>
                                    @endif

                                    @if($product->cost_price > 0)
                                        @php
                                            $effectivePrice = $product->effective_price;
                                            $margin = $effectivePrice > 0 ? round((($effectivePrice - $product->cost_price) / $effectivePrice) * 100, 1) : 0;
                                        @endphp
                                        <div class="text-[10px] text-slate-400">
                                            Cost: ${{ number_format($product->cost_price, 2) }}
                                            <span class="text-emerald-600 font-medium">({{ $margin }}% margin)</span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Stock Level & Quick Edit -->
                            <td class="px-5 py-3.5 whitespace-nowrap" id="product-stock-cell-{{ $product->id }}">
                                <div class="flex items-center gap-2">
                                    @if($product->stock_quantity <= 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200">
                                            <i data-lucide="x-circle" class="w-3.5 h-3.5 text-rose-500"></i>
                                            <span>Out of stock (0)</span>
                                        </span>
                                    @elseif($product->is_low_stock)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-bold border border-amber-200">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-amber-500"></i>
                                            <span>Low: {{ $product->stock_quantity }} {{ $product->unit }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-semibold border border-emerald-200">
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>{{ $product->stock_quantity }} {{ $product->unit }}</span>
                                        </span>
                                    @endif

                                    <button
                                        type="button"
                                        onclick="openQuickStockModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->stock_quantity }}, '{{ $product->unit }}')"
                                        class="p-1 rounded-md text-slate-400 hover:text-emerald-600 hover:bg-slate-100 transition-colors"
                                        title="Adjust Stock"
                                    >
                                        <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- Featured Star Toggle -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <button
                                    type="button"
                                    onclick="toggleProductFeatured({{ $product->id }}, this)"
                                    class="p-1.5 rounded-lg text-slate-300 hover:text-amber-500 hover:bg-amber-50 transition-colors {{ $product->is_featured ? 'text-amber-400!' : '' }}"
                                    title="Toggle featured status"
                                >
                                    <i data-lucide="star" class="w-4 h-4 {{ $product->is_featured ? 'fill-amber-400' : '' }}"></i>
                                </button>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        {{ $product->is_active ? 'checked' : '' }}
                                        onchange="toggleProductStatus({{ $product->id }}, this)"
                                        class="sr-only peer"
                                    />
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.products.edit', $product) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                        title="Edit Product"
                                    >
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <button
                                        type="button"
                                        onclick="confirmProductDelete({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->orderItems()->count() }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Delete Product"
                                    >
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <x-admin.empty-state
                                    title="No Products Found"
                                    description="No grocery items match your search filters. Add a new product to populate your catalog."
                                    icon="package-plus"
                                    actionText="Add New Product"
                                    :actionUrl="route('admin.products.create')"
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

    <!-- Quick Stock Modal -->
    <div id="quick-stock-modal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Adjust Stock Level</h3>
                        <p class="text-[11px] text-slate-500 truncate max-w-[280px]" id="modal-product-name">Product Name</p>
                    </div>
                </div>
                <button type="button" onclick="closeQuickStockModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form id="quick-stock-form" onsubmit="handleQuickStockSubmit(event)" class="p-5 space-y-4">
                <input type="hidden" id="modal-product-id" name="product_id" value="" />

                <!-- Action Type -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Adjustment Mode</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="border border-slate-200 rounded-xl p-2.5 text-center cursor-pointer hover:bg-slate-50 has-checked:border-emerald-500 has-checked:bg-emerald-50/50 has-checked:text-emerald-800 transition-colors">
                            <input type="radio" name="adjustment_type" value="set" checked class="sr-only" />
                            <span class="block text-xs font-semibold">Set Exact</span>
                        </label>
                        <label class="border border-slate-200 rounded-xl p-2.5 text-center cursor-pointer hover:bg-slate-50 has-checked:border-emerald-500 has-checked:bg-emerald-50/50 has-checked:text-emerald-800 transition-colors">
                            <input type="radio" name="adjustment_type" value="add" class="sr-only" />
                            <span class="block text-xs font-semibold">Add (+)</span>
                        </label>
                        <label class="border border-slate-200 rounded-xl p-2.5 text-center cursor-pointer hover:bg-slate-50 has-checked:border-emerald-500 has-checked:bg-emerald-50/50 has-checked:text-emerald-800 transition-colors">
                            <input type="radio" name="adjustment_type" value="subtract" class="sr-only" />
                            <span class="block text-xs font-semibold">Deduct (-)</span>
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
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Audit Reason / Reference Note</label>
                    <input
                        type="text"
                        id="modal-reason-input"
                        name="reason"
                        placeholder="e.g. Stock intake, Damaged items write-off"
                        class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden transition-all"
                    />
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <x-admin.button type="button" variant="outline" size="sm" onclick="closeQuickStockModal()">
                        Cancel
                    </x-admin.button>
                    <x-admin.button type="submit" variant="primary" size="sm" icon="check">
                        Apply Stock Update
                    </x-admin.button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Product Form (Hidden) -->
    <form id="delete-product-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    // AJAX Quick Status Toggle with jQuery
    function toggleProductStatus(id, checkbox) {
        const $checkbox = $(checkbox);
        $.ajax({
            url: `/admin/products/${id}/toggle-status`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'success', title: 'Status Updated', message: data.message });
                    }
                } else {
                    $checkbox.prop('checked', !$checkbox.prop('checked'));
                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to update status.' });
                    }
                }
            },
            error: function() {
                $checkbox.prop('checked', !$checkbox.prop('checked'));
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'error', title: 'Server Error', message: 'Could not connect to server.' });
                }
            }
        });
    }

    // AJAX Quick Featured Toggle with jQuery
    function toggleProductFeatured(id, btn) {
        const $btn = $(btn);
        $.ajax({
            url: `/admin/products/${id}/toggle-featured`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    const $icon = $btn.find('svg, i');
                    if (data.is_featured) {
                        $btn.addClass('text-amber-400');
                        $icon.addClass('fill-amber-400');
                    } else {
                        $btn.removeClass('text-amber-400');
                        $icon.removeClass('fill-amber-400');
                    }
                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'success', title: 'Featured Updated', message: data.message });
                    }
                }
            },
            error: function() {
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'error', title: 'Server Error', message: 'Could not update featured flag.' });
                }
            }
        });
    }

    // Quick Stock Adjustment Modal Controls with jQuery
    function openQuickStockModal(id, name, currentStock, unit) {
        $('#modal-product-id').val(id);
        $('#modal-product-name').text(name);
        $('#modal-product-unit').text(unit || 'units');
        $('#modal-quantity-input').val(currentStock);
        $('#modal-reason-input').val('');
        $('#quick-stock-modal').removeClass('hidden');
    }

    function closeQuickStockModal() {
        $('#quick-stock-modal').addClass('hidden');
    }

    function handleQuickStockSubmit(e) {
        e.preventDefault();
        const id = $('#modal-product-id').val();
        const formData = new FormData($('#quick-stock-form')[0]);

        $.ajax({
            url: `/admin/products/${id}/quick-stock`,
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
                        Admin.toast({ type: 'success', title: 'Stock Updated', message: data.message });
                    }
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to update stock.' });
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

    // SweetAlert2 Delete Confirmation with jQuery
    function confirmProductDelete(id, name, ordersCount) {
        if (ordersCount > 0) {
            Swal.fire({
                title: 'Cannot Delete Product',
                text: `"${name}" is referenced in ${ordersCount} customer order(s). To preserve order invoice integrity, please set status to Inactive instead of deleting.`,
                icon: 'warning',
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'Understand'
            });
            return;
        }

        if (window.Admin && window.Admin.confirm) {
            Admin.confirm({
                title: `Delete Product?`,
                text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc2626',
                onConfirm: () => {
                    $('#delete-product-form').attr('action', `/admin/products/${id}`).trigger('submit');
                }
            });
        }
    }
</script>
@endpush
