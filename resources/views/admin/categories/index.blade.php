@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Categories"
        subtitle="Manage grocery department hierarchy, product categories, and organization."
        :breadcrumbs="[
            ['title' => 'Categories', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.categories.create')"
                variant="primary"
                size="sm"
                icon="plus"
            >
                Add Category
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- KPI Metric Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Categories</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['total'] }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <i data-lucide="folder-tree" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Root Departments</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['root'] }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="git-branch" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Sub-Categories</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['sub'] }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active Status</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['active'] }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.categories.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
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
                        placeholder="Search category name, slug..."
                        class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                    />
                </div>
            </div>

            <!-- Parent Filter -->
            <div class="lg:col-span-3">
                <select
                    name="parent_id"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Hierarchy Levels</option>
                    <option value="root" {{ request('parent_id') === 'root' ? 'selected' : '' }}>Root Departments Only</option>
                    @foreach($rootCategories as $root)
                        <option value="{{ $root->id }}" {{ request('parent_id') == $root->id ? 'selected' : '' }}>
                            Sub-categories of {{ $root->name }}
                        </option>
                    @endforeach
                </select>
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

            <!-- Filter Buttons -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <x-admin.button type="submit" variant="secondary" size="sm" class="w-full sm:w-auto">
                    Filter
                </x-admin.button>
                @if(request()->hasAny(['search', 'parent_id', 'status', 'featured']))
                    <a
                        href="{{ route('admin.categories.index') }}"
                        class="p-2 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Clear filters"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.card>

    <!-- Categories Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Hierarchy / Parent</th>
                        <th class="px-5 py-3">Products</th>
                        <th class="px-5 py-3">Sort Order</th>
                        <th class="px-5 py-3 text-center">Featured</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/70 transition-colors" id="category-row-{{ $category->id }}">
                            <!-- Category Details -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100/60 text-emerald-600 flex items-center justify-center shrink-0 overflow-hidden shadow-xs">
                                        @if($category->image)
                                            <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover" />
                                        @else
                                            <i data-lucide="{{ $category->icon ?: 'shopping-bag' }}" class="w-5 h-5"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.categories.edit', $category) }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors truncate">
                                                {{ $category->name }}
                                            </a>
                                            @if($category->is_featured)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60">
                                                    Featured
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $category->slug }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Parent Category Badge -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($category->parent)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-medium">
                                        <i data-lucide="corner-down-right" class="w-3 h-3 text-slate-400"></i>
                                        <span>{{ $category->parent->name }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold border border-emerald-100">
                                        <i data-lucide="folder" class="w-3 h-3 text-emerald-500"></i>
                                        <span>Main Department</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Product Count -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 text-xs font-semibold">
                                    {{ $category->products_count }} {{ Str::plural('item', $category->products_count) }}
                                </span>
                            </td>

                            <!-- Sort Order -->
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-600 font-mono">
                                #{{ $category->sort_order }}
                            </td>

                            <!-- Featured Star Toggle -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <button
                                    type="button"
                                    onclick="toggleCategoryFeatured({{ $category->id }}, this)"
                                    class="p-1.5 rounded-lg text-slate-300 hover:text-amber-500 hover:bg-amber-50 transition-colors {{ $category->is_featured ? 'text-amber-400!' : '' }}"
                                    title="Toggle featured status"
                                >
                                    <i data-lucide="star" class="w-4 h-4 {{ $category->is_featured ? 'fill-amber-400' : '' }}"></i>
                                </button>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        {{ $category->is_active ? 'checked' : '' }}
                                        onchange="toggleCategoryStatus({{ $category->id }}, this)"
                                        class="sr-only peer"
                                    />
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.categories.edit', $category) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                        title="Edit Category"
                                    >
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <button
                                        type="button"
                                        onclick="confirmCategoryDelete({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->products_count }}, {{ $category->children->count() }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Delete Category"
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
                                    title="No Categories Found"
                                    description="No grocery categories match your search filters. Create a new category to get started."
                                    icon="folder-plus"
                                    actionText="Add New Category"
                                    :actionUrl="route('admin.categories.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
        @endif
    </x-admin.card>

    <!-- Delete Category Form (Hidden) -->
    <form id="delete-category-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    // AJAX Quick Status Toggle
    function toggleCategoryStatus(id, checkbox) {
        const url = `/admin/categories/${id}/toggle-status`;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'success', title: 'Status Updated', message: data.message });
                }
            } else {
                checkbox.checked = !checkbox.checked;
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to update status.' });
                }
            }
        })
        .catch(err => {
            checkbox.checked = !checkbox.checked;
            if (window.Admin && window.Admin.toast) {
                Admin.toast({ type: 'error', title: 'Server Error', message: 'Could not connect to server.' });
            }
        });
    }

    // AJAX Quick Featured Toggle
    function toggleCategoryFeatured(id, btn) {
        const url = `/admin/categories/${id}/toggle-featured`;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const icon = btn.querySelector('svg, i');
                if (data.is_featured) {
                    btn.classList.add('text-amber-400');
                    if (icon) icon.classList.add('fill-amber-400');
                } else {
                    btn.classList.remove('text-amber-400');
                    if (icon) icon.classList.remove('fill-amber-400');
                }
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'success', title: 'Featured Updated', message: data.message });
                }
            }
        })
        .catch(err => {
            if (window.Admin && window.Admin.toast) {
                Admin.toast({ type: 'error', title: 'Server Error', message: 'Could not update featured flag.' });
            }
        });
    }

    // SweetAlert2 Delete Confirmation
    function confirmCategoryDelete(id, name, productsCount, childrenCount) {
        if (productsCount > 0) {
            Swal.fire({
                title: 'Cannot Delete Category',
                text: `"${name}" currently has ${productsCount} assigned product(s). Please reassign or delete the products before removing this category.`,
                icon: 'warning',
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'Understand'
            });
            return;
        }

        if (childrenCount > 0) {
            Swal.fire({
                title: 'Cannot Delete Category',
                text: `"${name}" contains ${childrenCount} sub-category(s). Please remove or reassign the sub-categories first.`,
                icon: 'warning',
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'Understand'
            });
            return;
        }

        if (window.Admin && window.Admin.confirm) {
            Admin.confirm({
                title: `Delete Category?`,
                text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc2626',
                onConfirm: () => {
                    const form = document.getElementById('delete-category-form');
                    form.action = `/admin/categories/${id}`;
                    form.submit();
                }
            });
        } else {
            if (confirm(`Are you sure you want to delete "${name}"?`)) {
                const form = document.getElementById('delete-category-form');
                form.action = `/admin/categories/${id}`;
                form.submit();
            }
        }
    }
</script>
@endpush
