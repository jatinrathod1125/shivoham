@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Edit Product: {{ $product->name }}"
        subtitle="Update grocery item catalog specifications, pricing, stock levels, and media."
        :breadcrumbs="[
            ['title' => 'Products', 'url' => route('admin.products.index')],
            ['title' => $product->name, 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.products.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Back to Products
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Main Column -->
            <div class="lg:col-span-8 space-y-6">
                <!-- 1. Basic Information -->
                <x-admin.card title="Basic Information" subtitle="Item identity, categorization, and descriptions" icon="info">
                    <div class="space-y-4">
                        <!-- Product Name -->
                        <x-form.input
                            name="name"
                            label="Product Name"
                            placeholder="e.g. Organic Strawberries, Whole Milk 1 Gallon"
                            :required="true"
                            :value="old('name', $product->name)"
                            id="product-name-input"
                        />

                        <!-- Slug and SKU in 2-column grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.input
                                    name="slug"
                                    label="URL Slug"
                                    placeholder="e.g. organic-strawberries"
                                    :value="old('slug', $product->slug)"
                                    id="product-slug-input"
                                    helper="Unique URL identifier."
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                                    <span>Stock Keeping Unit (SKU)</span>
                                    <button
                                        type="button"
                                        onclick="generateRandomSKU()"
                                        class="text-[11px] text-emerald-600 hover:text-emerald-700 font-medium cursor-pointer"
                                    >
                                        Re-generate SKU
                                    </button>
                                </label>
                                <input
                                    type="text"
                                    name="sku"
                                    id="product-sku-input"
                                    placeholder="e.g. PRD-STRW-400"
                                    value="{{ old('sku', $product->sku) }}"
                                    class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-mono focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden transition-all"
                                />
                            </div>
                        </div>

                        <!-- Barcode and Weight -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form.input
                                name="barcode"
                                label="Barcode / UPC / EAN"
                                placeholder="e.g. 8901234567890"
                                :value="old('barcode', $product->barcode)"
                                icon="scan-line"
                                helper="Scannable barcode string for POS or inventory tracking."
                            />

                            <x-form.input
                                name="weight"
                                label="Weight / Net Package Content"
                                placeholder="e.g. 400g, 1kg, 750ml"
                                :value="old('weight', $product->weight)"
                                icon="scale"
                                helper="Display weight or pack size for grocery shoppers."
                            />
                        </div>

                        <!-- Category & Brand -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.select
                                    name="category_id"
                                    label="Category Department"
                                    helper="Select primary grocery shelf department."
                                >
                                    <option value="">Uncategorized</option>
                                    @foreach($categories as $parentCat)
                                        <option value="{{ $parentCat->id }}" {{ old('category_id', $product->category_id) == $parentCat->id ? 'selected' : '' }}>
                                            📁 {{ $parentCat->name }}
                                        </option>
                                        @foreach($parentCat->children as $childCat)
                                            <option value="{{ $childCat->id }}" {{ old('category_id', $product->category_id) == $childCat->id ? 'selected' : '' }}>
                                                &nbsp;&nbsp;&nbsp;&nbsp;↳ {{ $childCat->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </x-form.select>
                            </div>

                            <div>
                                <x-form.select
                                    name="brand_id"
                                    label="Manufacturer / Brand"
                                    helper="Select verified grocery brand or farm."
                                >
                                    <option value="">No Brand (Generic / Farm Fresh)</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                            🏷️ {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>

                        <!-- Short Description -->
                        <x-form.textarea
                            name="short_description"
                            label="Short Summary"
                            placeholder="A concise 1-2 sentence highlight for catalog cards..."
                            rows="2"
                            :value="old('short_description', $product->short_description)"
                        />

                        <!-- Full Description -->
                        <x-form.textarea
                            name="description"
                            label="Full Product Description & Nutrition Info"
                            placeholder="Detailed product specifications, origin, nutritional facts, and storage instructions..."
                            rows="4"
                            :value="old('description', $product->description)"
                        />
                    </div>
                </x-admin.card>

                <!-- 2. Pricing & Economics -->
                <x-admin.card title="Pricing & Profit Margin" subtitle="Configure wholesale cost, regular price, and sale promotions" icon="dollar-sign">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Cost Price -->
                            <x-form.input
                                type="number"
                                step="0.01"
                                name="cost_price"
                                label="Cost Price ($)"
                                placeholder="0.00"
                                :value="old('cost_price', $product->cost_price)"
                                id="cost-price-input"
                                icon="wallet"
                                helper="Wholesale procurement cost per unit."
                            />

                            <!-- Selling Price -->
                            <x-form.input
                                type="number"
                                step="0.01"
                                name="selling_price"
                                label="Regular Selling Price ($)"
                                placeholder="0.00"
                                :required="true"
                                :value="old('selling_price', $product->selling_price)"
                                id="selling-price-input"
                                icon="tag"
                                helper="Retail customer shelf price."
                            />
                        </div>

                        <!-- Live Gross Margin Calculation Widget -->
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100/70 text-emerald-700 flex items-center justify-center font-bold text-xs">
                                    %
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-800">Estimated Gross Margin</p>
                                    <p class="text-[11px] text-slate-500">Based on Cost vs Selling Price</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-base font-bold text-emerald-600" id="margin-percentage-badge">0.0%</span>
                                <span class="text-xs text-slate-400 block" id="margin-amount-badge">($0.00 / unit)</span>
                            </div>
                        </div>

                        <!-- Special / Sale Promotional Price -->
                        <div class="pt-3 border-t border-slate-100 space-y-3">
                            <p class="text-xs font-semibold text-slate-800">Promotional Discount (Optional)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <x-form.input
                                    type="number"
                                    step="0.01"
                                    name="special_price"
                                    label="Sale Price ($)"
                                    placeholder="0.00"
                                    :value="old('special_price', $product->special_price)"
                                    helper="Must be less than regular price."
                                />

                                <x-form.input
                                    type="datetime-local"
                                    name="special_price_start"
                                    label="Sale Starts At"
                                    :value="old('special_price_start', $product->special_price_start?->format('Y-m-d\TH:i'))"
                                />

                                <x-form.input
                                    type="datetime-local"
                                    name="special_price_end"
                                    label="Sale Ends At"
                                    :value="old('special_price_end', $product->special_price_end?->format('Y-m-d\TH:i'))"
                                />
                            </div>
                        </div>
                    </div>
                </x-admin.card>

                <!-- 3. Inventory & Logistics -->
                <x-admin.card title="Inventory & Stock Setup" subtitle="Warehouse stock quantity and low-stock triggers" icon="boxes">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-form.input
                            type="number"
                            name="stock_quantity"
                            label="Current Stock Quantity"
                            placeholder="0"
                            :required="true"
                            :value="old('stock_quantity', $product->stock_quantity)"
                            min="0"
                            helper="Editing here automatically logs an audit transaction."
                        />

                        <x-form.input
                            type="number"
                            name="min_stock_threshold"
                            label="Low Stock Warning Limit"
                            placeholder="10"
                            :value="old('min_stock_threshold', $product->min_stock_threshold)"
                            min="0"
                            helper="Triggers warning badge on dashboard."
                        />

                        <div>
                            <x-form.select
                                name="unit"
                                label="Unit of Measure"
                                helper="Physical packaging unit."
                            >
                                @foreach($units as $unitOption)
                                    <option value="{{ $unitOption }}" {{ old('unit', $product->unit) == $unitOption ? 'selected' : '' }}>
                                        {{ $unitOption }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>
                </x-admin.card>

                <!-- 4. Recent Stock Audit Transactions -->
                @if($recentTransactions->isNotEmpty())
                    <x-admin.card title="Recent Inventory Audit History" subtitle="Automated ledger of stock intake, order sales, and manual adjustments" icon="history">
                        <div class="overflow-x-auto -mx-5 -my-5">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                                    <tr>
                                        <th class="px-5 py-2.5">Date</th>
                                        <th class="px-5 py-2.5">Type</th>
                                        <th class="px-5 py-2.5">Qty Change</th>
                                        <th class="px-5 py-2.5">Stock Level</th>
                                        <th class="px-5 py-2.5">Reason / Ref</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                                    @foreach($recentTransactions as $tx)
                                        <tr>
                                            <td class="px-5 py-2.5 text-slate-500 font-sans">{{ $tx->created_at->format('M d, H:i') }}</td>
                                            <td class="px-5 py-2.5 font-sans">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $tx->type === 'addition' ? 'bg-emerald-50 text-emerald-700' : ($tx->type === 'order' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                                    {{ $tx->type }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-2.5 font-bold {{ $tx->type === 'addition' ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $tx->type === 'addition' ? '+' : '-' }}{{ $tx->quantity }}
                                            </td>
                                            <td class="px-5 py-2.5 text-slate-700">
                                                {{ $tx->previous_stock }} ➔ {{ $tx->current_stock }}
                                            </td>
                                            <td class="px-5 py-2.5 text-slate-500 font-sans truncate max-w-[200px]">
                                                {{ $tx->reason }} ({{ $tx->reference_id }})
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-admin.card>
                @endif
            </div>

            <!-- Right Sidebar Column -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Status & Organization -->
                <x-admin.card title="Visibility & Settings" icon="settings">
                    <div class="space-y-4">
                        <!-- Active Status Switch -->
                        <x-form.switch
                            name="is_active"
                            label="Product Status"
                            description="Enable to publish this item live in grocery catalog."
                            :checked="old('is_active', $product->is_active)"
                        />

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Featured Switch -->
                            <x-form.switch
                                name="is_featured"
                                label="Featured Item"
                                description="Display in homepage bestsellers and highlighted carousel."
                                :checked="old('is_featured', $product->is_featured)"
                            />
                        </div>

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Sort Order -->
                            <x-form.input
                                type="number"
                                name="sort_order"
                                label="Display Sort Order"
                                placeholder="0"
                                :value="old('sort_order', $product->sort_order)"
                                min="0"
                                helper="Lower numbers appear first in catalog."
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Media Uploads -->
                <x-admin.card title="Primary Image" subtitle="Main product photograph" icon="image">
                    @if($product->thumbnail)
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Current Thumbnail</label>
                            <div class="relative w-full h-44 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 shadow-xs">
                                <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    @endif

                    <x-form.file-upload
                        name="thumbnail"
                        :label="$product->thumbnail ? 'Replace Thumbnail' : 'Upload Thumbnail (JPG, PNG, WebP)'"
                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                        helper="Square ratio recommended (800x800px). Max 2MB."
                    />
                </x-admin.card>

                <!-- Form Action Buttons -->
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <x-admin.button
                            type="submit"
                            variant="primary"
                            size="md"
                            icon="check"
                            class="flex-1"
                        >
                            Save Changes
                        </x-admin.button>

                        <x-admin.button
                            :href="route('admin.products.index')"
                            variant="outline"
                            size="md"
                        >
                            Cancel
                        </x-admin.button>
                    </div>

                    <div class="pt-2">
                        <button
                            type="button"
                            onclick="confirmProductDelete({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->orderItems()->count() }})"
                            class="w-full px-4 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Delete Product</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Product Form (Hidden) -->
    <form id="delete-product-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    $(function () {
        // Live Margin Calculator with jQuery
        const $costInput = $('#cost-price-input');
        const $sellInput = $('#selling-price-input');
        const $marginPct = $('#margin-percentage-badge');
        const $marginAmt = $('#margin-amount-badge');

        function recalculateMargin() {
            const cost = parseFloat($costInput.val()) || 0;
            const sell = parseFloat($sellInput.val()) || 0;

            if (sell > 0) {
                const profit = sell - cost;
                const pct = ((profit / sell) * 100).toFixed(1);
                $marginPct.text(`${pct}%`);
                $marginAmt.text(`($${profit.toFixed(2)} / unit)`);

                if (pct < 0) {
                    $marginPct.attr('class', 'text-base font-bold text-rose-600');
                } else if (pct < 15) {
                    $marginPct.attr('class', 'text-base font-bold text-amber-600');
                } else {
                    $marginPct.attr('class', 'text-base font-bold text-emerald-600');
                }
            } else {
                $marginPct.text('0.0%');
                $marginAmt.text('($0.00 / unit)');
                $marginPct.attr('class', 'text-base font-bold text-slate-400');
            }
        }

        $costInput.on('input', recalculateMargin);
        $sellInput.on('input', recalculateMargin);
        recalculateMargin();
    });

    function generateRandomSKU() {
        const nameClean = ($('#product-name-input').val() || 'ITEM').replace(/[^A-Za-z]/g, '').toUpperCase().substring(0, 4) || 'PRD';
        const randNum = Math.floor(100 + Math.random() * 900);
        $('#product-sku-input').val(`PRD-${nameClean}-${randNum}`);
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
