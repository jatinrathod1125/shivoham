@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Add New Brand"
        subtitle="Register a new grocery brand, organic manufacturer, or local farm label."
        :breadcrumbs="[
            ['title' => 'Brands', 'url' => route('admin.brands.index')],
            ['title' => 'Add Brand', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.brands.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Back to Brands
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Main Column -->
            <div class="lg:col-span-8 space-y-6">
                <x-admin.card title="Brand Information" subtitle="Primary company details, website and bio" icon="info">
                    <div class="space-y-4">
                        <!-- Brand Name -->
                        <x-form.input
                            name="name"
                            label="Brand Name"
                            placeholder="e.g. Organic Valley, Kerrygold, Oatly"
                            :required="true"
                            :value="old('name')"
                            id="brand-name-input"
                        />

                        <!-- Slug -->
                        <x-form.input
                            name="slug"
                            label="URL Slug"
                            placeholder="e.g. organic-valley"
                            :value="old('slug')"
                            id="brand-slug-input"
                            helper="Unique URL identifier. Automatically generated from brand name if left blank."
                        />

                        <!-- Website URL -->
                        <x-form.input
                            type="url"
                            name="website"
                            label="Official Website URL"
                            placeholder="https://www.example.com"
                            :value="old('website')"
                            icon="globe"
                            helper="Manufacturer or farm home website for customer reference."
                        />

                        <!-- Description -->
                        <x-form.textarea
                            name="description"
                            label="Brand Bio / Description"
                            placeholder="Provide details about this grocery supplier's standards, organic certifications, and history..."
                            rows="4"
                            :value="old('description')"
                        />
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Sidebar Column -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Status & Visibility -->
                <x-admin.card title="Visibility & Settings" icon="settings">
                    <div class="space-y-4">
                        <!-- Active Status Switch -->
                        <x-form.switch
                            name="is_active"
                            label="Brand Status"
                            description="Enable to make this brand and its products active across the store."
                            :checked="old('is_active', true)"
                        />

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Featured Switch -->
                            <x-form.switch
                                name="is_featured"
                                label="Featured Brand"
                                description="Display in featured brand carousels on homepage and catalog filters."
                                :checked="old('is_featured', false)"
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Brand Logo Upload -->
                <x-admin.card title="Brand Logo" subtitle="Upload official brand emblem or manufacturer badge" icon="image">
                    <x-form.file-upload
                        name="logo"
                        label="Logo Image (PNG, WebP, JPG, SVG)"
                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                        helper="Square ratio recommended (400x400px). Transparent PNG or SVG preferred."
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
                        Save Brand
                    </x-admin.button>

                    <x-admin.button
                        :href="route('admin.brands.index')"
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
    // Auto-generate URL slug from Brand Name input
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('brand-name-input');
        const slugInput = document.getElementById('brand-slug-input');

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
