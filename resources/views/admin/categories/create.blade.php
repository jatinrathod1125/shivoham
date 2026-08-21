@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Add New Category"
        subtitle="Create a new grocery department or sub-category."
        :breadcrumbs="[
            ['title' => 'Categories', 'url' => route('admin.categories.index')],
            ['title' => 'Add Category', 'url' => '']
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

    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
        @csrf

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
                            :value="old('name')"
                            id="category-name-input"
                        />

                        <!-- Slug -->
                        <div>
                            <x-form.input
                                name="slug"
                                label="URL Slug"
                                placeholder="e.g. fresh-fruits-vegetables"
                                :value="old('slug')"
                                id="category-slug-input"
                                helper="Unique URL identifier. Automatically generated from category name if left blank."
                            />
                        </div>

                        <!-- Parent Category Selector -->
                        <div>
                            <x-form.select
                                name="parent_id"
                                label="Parent Department / Category"
                                helper="Leave as 'None (Main Root Department)' to create a top-level grocery section."
                            >
                                <option value="">None (Main Root Department)</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
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
                            :value="old('description')"
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
                                :value="old('icon', 'shopping-bag')"
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
                            :checked="old('is_active', true)"
                        />

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Featured Switch -->
                            <x-form.switch
                                name="is_featured"
                                label="Featured Category"
                                description="Display in featured grocery carousels and top department bars."
                                :checked="old('is_featured', false)"
                            />
                        </div>

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Sort Order -->
                            <x-form.input
                                type="number"
                                name="sort_order"
                                label="Sort Order Display Priority"
                                placeholder="0"
                                :value="old('sort_order', 0)"
                                min="0"
                                helper="Lower numbers appear first in lists and navigations."
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Category Image Upload -->
                <x-admin.card title="Category Image" subtitle="Upload department banner or thumbnail (JPG, PNG, WebP)" icon="image">
                    <x-form.file-upload
                        name="image"
                        label="Department Photo / Banner"
                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                        helper="Recommended dimensions: 600x600px. Max size 2MB."
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
                        Save Category
                    </x-admin.button>

                    <x-admin.button
                        :href="route('admin.categories.index')"
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
    // Auto-generate URL slug from Name input
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('category-name-input');
        const slugInput = document.getElementById('category-slug-input');

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
    });
</script>
@endpush
