@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Edit Category: {{ $category->name }}"
        subtitle="Update department hierarchy, title, slug, and status."
        :breadcrumbs="[
            ['title' => 'Categories', 'url' => route('admin.categories.index')],
            ['title' => $category->name, 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.categories.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Back to Categories
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Main Column (Details & Hierarchy) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- General Info Card -->
                <x-admin.card title="General Information" subtitle="Primary category names, identifiers and description" icon="info">
                    <div class="space-y-4">
                        <!-- Category Name -->
                        <x-form.input
                            name="name"
                            label="Category Name"
                            placeholder="e.g. Fresh Fruits & Vegetables"
                            :required="true"
                            :value="old('name', $category->name)"
                            id="category-name-input"
                        />

                        <!-- Slug -->
                        <div>
                            <x-form.input
                                name="slug"
                                label="URL Slug"
                                placeholder="e.g. fresh-fruits-vegetables"
                                :value="old('slug', $category->slug)"
                                id="category-slug-input"
                                helper="Unique URL identifier. Changing this may affect existing category URLs."
                            />
                        </div>

                        <!-- Parent Category Selector -->
                        <div>
                            <x-form.select
                                name="parent_id"
                                label="Parent Department / Category"
                                helper="Select a top-level department or leave as 'None (Main Root Department)'."
                            >
                                <option value="">None (Main Root Department)</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                        📁 {{ $parent->name }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>

                        <!-- Description -->
                        <x-form.textarea
                            name="description"
                            label="Description"
                            placeholder="Provide a brief overview of the grocery items in this category..."
                            rows="4"
                            :value="old('description', $category->description)"
                        />
                    </div>
                </x-admin.card>

                <!-- Visual & Icon Settings -->
                <x-admin.card title="Icon & Appearance" subtitle="Select a Lucide icon representation for navigation" icon="sparkles">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-form.input
                                name="icon"
                                label="Lucide Icon Name"
                                placeholder="e.g. apple, milk, wheat, coffee"
                                :value="old('icon', $category->icon ?: 'shopping-bag')"
                                id="category-icon-input"
                                helper="Icon keyword from Lucide icon library."
                            />
                        </div>

                        <!-- Quick Icon Picker Shortcuts -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Quick Suggestions</label>
                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach(['apple', 'carrot', 'milk', 'wheat', 'coffee', 'beef', 'cookie', 'package', 'sparkles', 'shopping-bag'] as $suggestedIcon)
                                    <button
                                        type="button"
                                        onclick="document.getElementById('category-icon-input').value = '{{ $suggestedIcon }}';"
                                        class="px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 text-slate-700 text-xs font-medium transition-colors"
                                    >
                                        {{ $suggestedIcon }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-admin.card>

                <!-- Associated Products Preview / Info -->
                <x-admin.card title="Category Statistics" subtitle="Live catalog distribution" icon="bar-chart-2">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[11px] text-slate-500 font-semibold uppercase">Assigned Products</span>
                            <div class="text-lg font-bold text-slate-900 mt-1">{{ $category->products()->count() }} items</div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[11px] text-slate-500 font-semibold uppercase">Sub-Categories</span>
                            <div class="text-lg font-bold text-slate-900 mt-1">{{ $category->children()->count() }} departments</div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[11px] text-slate-500 font-semibold uppercase">Created At</span>
                            <div class="text-xs font-medium text-slate-700 mt-1.5">{{ $category->created_at?->format('M d, Y') ?? 'N/A' }}</div>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Sidebar Column (Media, Visibility & Actions) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Status & Visibility -->
                <x-admin.card title="Visibility & Settings" icon="settings">
                    <div class="space-y-4">
                        <!-- Active Status Switch -->
                        <x-form.switch
                            name="is_active"
                            label="Category Status"
                            description="Enable to make this category active and visible across the store."
                            :checked="old('is_active', $category->is_active)"
                        />

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Featured Switch -->
                            <x-form.switch
                                name="is_featured"
                                label="Featured Category"
                                description="Display in featured grocery carousels and top department bars."
                                :checked="old('is_featured', $category->is_featured)"
                            />
                        </div>

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Sort Order -->
                            <x-form.input
                                type="number"
                                name="sort_order"
                                label="Sort Order Display Priority"
                                placeholder="0"
                                :value="old('sort_order', $category->sort_order)"
                                min="0"
                                helper="Lower numbers appear first in lists and navigations."
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Category Image Upload -->
                <x-admin.card title="Category Image" subtitle="Department photo or banner" icon="image">
                    @if($category->image)
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Current Image</label>
                            <div class="relative w-full h-40 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 shadow-xs">
                                <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    @endif

                    <x-form.file-upload
                        name="image"
                        :label="$category->image ? 'Replace Image' : 'Department Photo / Banner'"
                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                        helper="Recommended dimensions: 600x600px. Max size 2MB."
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
                            :href="route('admin.categories.index')"
                            variant="outline"
                            size="md"
                        >
                            Cancel
                        </x-admin.button>
                    </div>

                    <div class="pt-2">
                        <button
                            type="button"
                            onclick="confirmCategoryDelete({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->products()->count() }}, {{ $category->children()->count() }})"
                            class="w-full px-4 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Delete Category</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Category Form (Hidden) -->
    <form id="delete-category-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
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
        }
    }
</script>
@endpush
