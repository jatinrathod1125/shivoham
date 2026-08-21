@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Brands"
        subtitle="Manage grocery product manufacturers, organic suppliers, and trusted farm labels."
        :breadcrumbs="[
            ['title' => 'Brands', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.brands.create')"
                variant="primary"
                size="sm"
                icon="plus"
            >
                Add Brand
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- KPI Metric Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="tag" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Brands</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['total'] }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="star" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Featured Brands</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['featured'] }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['active'] }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                <i data-lucide="pause-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Inactive</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['inactive'] }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.brands.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
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
                        placeholder="Search brand name, website, description..."
                        class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                    />
                </div>
            </div>

            <!-- Status Filter -->
            <div class="lg:col-span-2">
                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Featured Filter -->
            <div class="lg:col-span-2">
                <select
                    name="featured"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Brands</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured Only</option>
                    <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Non-Featured</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <x-admin.button type="submit" variant="secondary" size="sm" class="w-full sm:w-auto">
                    Filter
                </x-admin.button>
                @if(request()->hasAny(['search', 'status', 'featured']))
                    <a
                        href="{{ route('admin.brands.index') }}"
                        class="p-2 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Clear filters"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.card>

    <!-- Brands Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Brand</th>
                        <th class="px-5 py-3">Website</th>
                        <th class="px-5 py-3">Products</th>
                        <th class="px-5 py-3 text-center">Featured</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brands as $brand)
                        <tr class="hover:bg-slate-50/70 transition-colors" id="brand-row-{{ $brand->id }}">
                            <!-- Brand Details -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 text-slate-700 flex items-center justify-center shrink-0 overflow-hidden font-bold text-xs uppercase shadow-xs">
                                        @if($brand->logo)
                                            <img src="{{ $brand->logo }}" alt="{{ $brand->name }}" class="w-full h-full object-cover" />
                                        @else
                                            {{ strtoupper(substr($brand->name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.brands.edit', $brand) }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors truncate">
                                                {{ $brand->name }}
                                            </a>
                                            @if($brand->is_featured)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60">
                                                    Featured
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $brand->slug }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Website Link -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($brand->website)
                                    <a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer" class="text-xs text-emerald-600 hover:text-emerald-700 hover:underline flex items-center gap-1.5 truncate max-w-[200px]">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        <span class="truncate">{{ preg_replace('#^https?://#', '', rtrim($brand->website, '/')) }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[11px]">N/A</span>
                                @endif
                            </td>

                            <!-- Product Count -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 text-xs font-semibold">
                                    {{ $brand->products_count }} {{ Str::plural('product', $brand->products_count) }}
                                </span>
                            </td>

                            <!-- Featured Star Toggle -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <button
                                    type="button"
                                    onclick="toggleBrandFeatured({{ $brand->id }}, this)"
                                    class="p-1.5 rounded-lg text-slate-300 hover:text-amber-500 hover:bg-amber-50 transition-colors {{ $brand->is_featured ? 'text-amber-400!' : '' }}"
                                    title="Toggle featured status"
                                >
                                    <i data-lucide="star" class="w-4 h-4 {{ $brand->is_featured ? 'fill-amber-400' : '' }}"></i>
                                </button>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        {{ $brand->is_active ? 'checked' : '' }}
                                        onchange="toggleBrandStatus({{ $brand->id }}, this)"
                                        class="sr-only peer"
                                    />
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.brands.edit', $brand) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                        title="Edit Brand"
                                    >
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <button
                                        type="button"
                                        onclick="confirmBrandDelete({{ $brand->id }}, '{{ addslashes($brand->name) }}', {{ $brand->products_count }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Delete Brand"
                                    >
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <x-admin.empty-state
                                    title="No Brands Found"
                                    description="No grocery brands match your search filters. Create a new brand label to get started."
                                    icon="tag"
                                    actionText="Add New Brand"
                                    :actionUrl="route('admin.brands.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($brands->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $brands->links() }}
            </div>
        @endif
    </x-admin.card>

    <!-- Delete Brand Form (Hidden) -->
    <form id="delete-brand-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    // AJAX Quick Status Toggle with jQuery
    function toggleBrandStatus(id, checkbox) {
        const $checkbox = $(checkbox);
        $.ajax({
            url: `/admin/brands/${id}/toggle-status`,
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
    function toggleBrandFeatured(id, btn) {
        const $btn = $(btn);
        $.ajax({
            url: `/admin/brands/${id}/toggle-featured`,
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

    // SweetAlert2 Delete Confirmation with jQuery
    function confirmBrandDelete(id, name, productsCount) {
        if (productsCount > 0) {
            Swal.fire({
                title: 'Cannot Delete Brand',
                text: `"${name}" currently has ${productsCount} assigned product(s). Please reassign or delete the products before removing this brand.`,
                icon: 'warning',
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'Understand'
            });
            return;
        }

        if (window.Admin && window.Admin.confirm) {
            Admin.confirm({
                title: `Delete Brand?`,
                text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc2626',
                onConfirm: () => {
                    $('#delete-brand-form').attr('action', `/admin/brands/${id}`).trigger('submit');
                }
            });
        }
    }
</script>
@endpush
