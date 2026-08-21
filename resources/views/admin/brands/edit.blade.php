@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Edit Brand: {{ $brand->name }}"
        subtitle="Update brand identity, official link, and store display settings."
        :breadcrumbs="[
            ['title' => 'Brands', 'url' => route('admin.brands.index')],
            ['title' => $brand->name, 'url' => '']
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

    <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                            :value="old('name', $brand->name)"
                            id="brand-name-input"
                        />

                        <!-- Slug -->
                        <x-form.input
                            name="slug"
                            label="URL Slug"
                            placeholder="e.g. organic-valley"
                            :value="old('slug', $brand->slug)"
                            id="brand-slug-input"
                            helper="Unique URL identifier. Changing this may affect existing product filter URLs."
                        />

                        <!-- Website URL -->
                        <x-form.input
                            type="url"
                            name="website"
                            label="Official Website URL"
                            placeholder="https://www.example.com"
                            :value="old('website', $brand->website)"
                            icon="globe"
                            helper="Manufacturer or farm home website for customer reference."
                        />

                        <!-- Description -->
                        <x-form.textarea
                            name="description"
                            label="Brand Bio / Description"
                            placeholder="Provide details about this grocery supplier's standards, organic certifications, and history..."
                            rows="4"
                            :value="old('description', $brand->description)"
                        />
                    </div>
                </x-admin.card>

                <!-- Associated Products Preview / Info -->
                <x-admin.card title="Catalog Statistics" subtitle="Live brand distribution" icon="bar-chart-2">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[11px] text-slate-500 font-semibold uppercase">Assigned Products</span>
                            <div class="text-lg font-bold text-slate-900 mt-1">{{ $brand->products()->count() }} items</div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[11px] text-slate-500 font-semibold uppercase">Featured Status</span>
                            <div class="text-xs font-semibold text-slate-800 mt-1.5 flex items-center gap-1">
                                <i data-lucide="{{ $brand->is_featured ? 'star' : 'star-off' }}" class="w-4 h-4 {{ $brand->is_featured ? 'text-amber-500 fill-amber-400' : 'text-slate-400' }}"></i>
                                <span>{{ $brand->is_featured ? 'Featured on Store' : 'Standard Catalog' }}</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[11px] text-slate-500 font-semibold uppercase">Created At</span>
                            <div class="text-xs font-medium text-slate-700 mt-1.5">{{ $brand->created_at?->format('M d, Y') ?? 'N/A' }}</div>
                        </div>
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
                            :checked="old('is_active', $brand->is_active)"
                        />

                        <div class="pt-3 border-t border-slate-100">
                            <!-- Featured Switch -->
                            <x-form.switch
                                name="is_featured"
                                label="Featured Brand"
                                description="Display in featured brand carousels on homepage and catalog filters."
                                :checked="old('is_featured', $brand->is_featured)"
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Brand Logo Upload -->
                <x-admin.card title="Brand Logo" subtitle="Upload official brand emblem or manufacturer badge" icon="image">
                    @if($brand->logo)
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Current Logo</label>
                            <div class="relative w-full h-32 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center p-4 shadow-xs">
                                <img src="{{ $brand->logo }}" alt="{{ $brand->name }}" class="max-h-full max-w-full object-contain" />
                            </div>
                        </div>
                    @endif

                    <x-form.file-upload
                        name="logo"
                        :label="$brand->logo ? 'Replace Logo Image' : 'Logo Image (PNG, WebP, JPG, SVG)'"
                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                        helper="Square ratio recommended (400x400px). Transparent PNG or SVG preferred."
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
                            :href="route('admin.brands.index')"
                            variant="outline"
                            size="md"
                        >
                            Cancel
                        </x-admin.button>
                    </div>

                    <div class="pt-2">
                        <button
                            type="button"
                            onclick="confirmBrandDelete({{ $brand->id }}, '{{ addslashes($brand->name) }}', {{ $brand->products()->count() }})"
                            class="w-full px-4 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Delete Brand</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Brand Form (Hidden) -->
    <form id="delete-brand-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
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
