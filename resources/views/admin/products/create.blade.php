@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Add New Product"
        subtitle="Create a new grocery item, configure pricing, stock levels, and media."
        :breadcrumbs="[
            ['title' => 'Products', 'url' => route('admin.products.index')],
            ['title' => 'Add Product', 'url' => '']
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

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf

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
                            :value="old('name')"
                            id="product-name-input"
                        />

                        <!-- Slug and SKU in 2-column grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.input
                                    name="slug"
                                    label="URL Slug"
                                    placeholder="e.g. organic-strawberries"
                                    :value="old('slug')"
                                    id="product-slug-input"
                                    helper="Unique URL identifier. Auto-generated from name."
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
                                        Generate SKU
                                    </button>
                                </label>
                                <input
                                    type="text"
                                    name="sku"
                                    id="product-sku-input"
                                    placeholder="e.g. PRD-STRW-400"
                                    value="{{ old('sku') }}"
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
                                :value="old('barcode')"
                                icon="scan-line"
                                helper="Scannable barcode string for POS or inventory tracking."
                            />

                            <x-form.input
                                name="weight"
                                label="Weight / Net Package Content"
                                placeholder="e.g. 400g, 1kg, 750ml"
                                :value="old('weight')"
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
                                        <option value="{{ $parentCat->id }}" {{ old('category_id') == $parentCat->id ? 'selected' : '' }}>
                                            📁 {{ $parentCat->name }}
                                        </option>
                                        @foreach($parentCat->children as $childCat)
                                            <option value="{{ $childCat->id }}" {{ old('category_id') == $childCat->id ? 'selected' : '' }}>
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
                                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
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
                            :value="old('short_description')"
                        />

                        <!-- Full Description -->
                        <x-form.textarea
                            name="description"
                            label="Full Product Description & Nutrition Info"
                            placeholder="Detailed product specifications, origin, nutritional facts, and storage instructions..."
                            rows="4"
                            :value="old('description')"
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
                                :value="old('cost_price', '0.00')"
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
                                :value="old('selling_price')"
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
                                    :value="old('special_price')"
                                    helper="Must be less than regular price."
                                />

                                <x-form.input
                                    type="datetime-local"
                                    name="special_price_start"
                                    label="Sale Starts At"
                                    :value="old('special_price_start')"
                                />

                                <x-form.input
                                    type="datetime-local"
                                    name="special_price_end"
                                    label="Sale Ends At"
                                    :value="old('special_price_end')"
                                />
                            </div>
                        </div>
                    </div>
                </x-admin.card>

                <!-- 3. Inventory & Logistics -->
                <x-admin.card title="Inventory & Stock Setup" subtitle="Initial inventory quantities and low-stock triggers" icon="boxes">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-form.input
                            type="number"
                            name="stock_quantity"
                            label="Initial Stock Quantity"
                            placeholder="0"
                            :required="true"
                            :value="old('stock_quantity', 20)"
                            min="0"
                            helper="Auto-logged into inventory audit history."
                        />

                        <x-form.input
                            type="number"
                            name="min_stock_threshold"
                            label="Low Stock Warning Limit"
                            placeholder="10"
                            :value="old('min_stock_threshold', 10)"
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
                                    <option value="{{ $unitOption }}" {{ old('unit', 'pcs') == $unitOption ? 'selected' : '' }}>
                                        {{ $unitOption }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>
                </x-admin.card>
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
                            :checked="old('is_active', true)"
                        />

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Featured Switch -->
                            <x-form.switch
                                name="is_featured"
                                label="Featured Item"
                                description="Display in homepage bestsellers and highlighted carousel."
                                :checked="old('is_featured', false)"
                            />
                        </div>

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Sort Order -->
                            <x-form.input
                                type="number"
                                name="sort_order"
                                label="Display Sort Order"
                                placeholder="0"
                                :value="old('sort_order', 0)"
                                min="0"
                                helper="Lower numbers appear first in catalog."
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Media Uploads -->
                <x-admin.card title="Primary Image" subtitle="Main product photograph" icon="image">
                    <x-form.file-upload
                        name="thumbnail"
                        label="Upload Thumbnail (JPG, PNG, WebP)"
                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                        helper="Square ratio recommended (800x800px). Max 2MB."
                    />
                </x-admin.card>

                <!-- Form Action Buttons -->
                <div class="flex items-center gap-3">
                    <x-admin.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="check"
                        class="flex-1"
                    >
                        Save Product
                    </x-admin.button>

                    <x-admin.button
                        :href="route('admin.products.index')"
                        variant="outline"
                        size="md"
                    >
                        Cancel
                    </x-admin.button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-generate URL slug from Product Name
        const nameInput = document.getElementById('product-name-input');
        const slugInput = document.getElementById('product-slug-input');

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function () {
                if (!slugInput.dataset.manualEdit) {
                    slugInput.value = nameInput.value
                        .toLowerCase()
                        .trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            });

            slugInput.addEventListener('input', function () {
                slugInput.dataset.manualEdit = 'true';
            });
        }

        // Live Margin Calculator
        const costInput = document.getElementById('cost-price-input');
        const sellInput = document.getElementById('selling-price-input');
        const marginPct = document.getElementById('margin-percentage-badge');
        const marginAmt = document.getElementById('margin-amount-badge');

        function recalculateMargin() {
            const cost = parseFloat(costInput?.value) || 0;
            const sell = parseFloat(sellInput?.value) || 0;

            if (sell > 0) {
                const profit = sell - cost;
                const pct = ((profit / sell) * 100).toFixed(1);
                marginPct.textContent = `${pct}%`;
                marginAmt.textContent = `($${profit.toFixed(2)} / unit)`;

                if (pct < 0) {
                    marginPct.className = 'text-base font-bold text-rose-600';
                } else if (pct < 15) {
                    marginPct.className = 'text-base font-bold text-amber-600';
                } else {
                    marginPct.className = 'text-base font-bold text-emerald-600';
                }
            } else {
                marginPct.textContent = '0.0%';
                marginAmt.textContent = '($0.00 / unit)';
                marginPct.className = 'text-base font-bold text-slate-400';
            }
        }

        costInput?.addEventListener('input', recalculateMargin);
        sellInput?.addEventListener('input', recalculateMargin);
        recalculateMargin();
    });

    function generateRandomSKU() {
        const nameInput = document.getElementById('product-name-input');
        const skuInput = document.getElementById('product-sku-input');
        const nameClean = (nameInput?.value || 'ITEM').replace(/[^A-Za-z]/g, '').toUpperCase().substring(0, 4) || 'PRD';
        const randNum = Math.floor(100 + Math.random() * 900);
        skuInput.value = `PRD-${nameClean}-${randNum}`;
    }
</script>
@endpush
