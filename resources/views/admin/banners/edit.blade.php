@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Edit Banner: {{ $banner->title }}"
        subtitle="Update banner placement, graphics, target link, and priority order."
        :breadcrumbs="[
            ['title' => 'Banners', 'url' => route('admin.banners.index')],
            ['title' => 'Edit Banner', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.banners.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Back to Banners
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Content & Placement -->
            <div class="lg:col-span-8 space-y-6">
                <x-admin.card title="Banner Content" subtitle="Headlines and call-to-action destinations" icon="image">
                    <div class="space-y-4">
                        <x-form.input
                            name="title"
                            label="Banner Main Headline"
                            placeholder="e.g. 100% Organic Farm Fresh Produce"
                            :required="true"
                            :value="old('title', $banner->title)"
                            helper="Prominent primary headline."
                        />

                        <x-form.input
                            name="subtitle"
                            label="Secondary Subtitle / Description"
                            placeholder="e.g. Delivered straight to your doorstep within 2 hours."
                            :value="old('subtitle', $banner->subtitle)"
                        />

                        <x-form.input
                            name="link"
                            label="Call to Action (CTA) URL Link"
                            placeholder="e.g. /categories/fresh-fruits or https://grocery.local/offers"
                            :value="old('link', $banner->link)"
                            helper="Destination page when shoppers click this banner."
                        />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.select
                                    name="position"
                                    label="Placement Position"
                                    :required="true"
                                >
                                    <option value="home_hero" {{ old('position', $banner->position) === 'home_hero' ? 'selected' : '' }}>Home Hero Slider (Top of Homepage)</option>
                                    <option value="promotional_bar" {{ old('position', $banner->position) === 'promotional_bar' ? 'selected' : '' }}>Promotional Strip Bar</option>
                                    <option value="category_top" {{ old('position', $banner->position) === 'category_top' ? 'selected' : '' }}>Category Page Header</option>
                                    <option value="sidebar" {{ old('position', $banner->position) === 'sidebar' ? 'selected' : '' }}>Sidebar Promo Widget</option>
                                    <option value="popup" {{ old('position', $banner->position) === 'popup' ? 'selected' : '' }}>Entrance Popup Modal</option>
                                </x-form.select>
                            </div>

                            <x-form.input
                                type="number"
                                min="0"
                                name="sort_order"
                                label="Sort Order / Priority"
                                placeholder="0"
                                :value="old('sort_order', $banner->sort_order)"
                                helper="Lower numbers appear first."
                            />
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Banner Upload, Schedule & Status -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Image Graphic Upload -->
                <x-admin.card title="Banner Image" subtitle="High-resolution graphic" icon="upload-cloud">
                    <div class="space-y-3">
                        <div
                            id="banner-dropzone"
                            onclick="document.getElementById('banner-file-input').click()"
                            class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-emerald-500 hover:bg-emerald-50/20 transition-all cursor-pointer group"
                        >
                            <input
                                type="file"
                                id="banner-file-input"
                                name="image"
                                accept="image/*"
                                class="hidden"
                                onchange="previewBannerGraphic(this)"
                            />
                            @if($banner->image)
                                <div id="banner-placeholder" class="hidden space-y-1.5 py-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-emerald-100 text-slate-500 group-hover:text-emerald-600 flex items-center justify-center mx-auto transition-colors">
                                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-700">Click to upload banner graphic</p>
                                </div>
                                <div id="banner-preview-box">
                                    <img id="banner-preview-img" src="{{ $banner->image }}" alt="{{ $banner->title }}" class="w-full h-32 object-cover rounded-lg border border-slate-200" />
                                    <p class="text-[10px] text-emerald-600 font-semibold mt-2">Click to replace banner graphic</p>
                                </div>
                            @else
                                <div id="banner-placeholder" class="space-y-1.5 py-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-emerald-100 text-slate-500 group-hover:text-emerald-600 flex items-center justify-center mx-auto transition-colors">
                                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-700">Click to upload banner graphic</p>
                                    <p class="text-[10px] text-slate-400">PNG, JPG, WebP up to 3MB</p>
                                </div>
                                <div id="banner-preview-box" class="hidden">
                                    <img id="banner-preview-img" src="" alt="Banner Preview" class="w-full h-32 object-cover rounded-lg border border-slate-200" />
                                    <p class="text-[10px] text-emerald-600 font-semibold mt-2">Click to replace banner</p>
                                </div>
                            @endif
                        </div>
                        @error('image')
                            <p class="text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </x-admin.card>

                <!-- Validity Schedule -->
                <x-admin.card title="Display Schedule" subtitle="Start and expiration dates" icon="calendar">
                    <div class="space-y-4">
                        <x-form.input
                            type="datetime-local"
                            name="starts_at"
                            label="Starts At"
                            :value="old('starts_at', $banner->starts_at?->format('Y-m-d\TH:i'))"
                            helper="Leave empty for immediate activation."
                        />

                        <x-form.input
                            type="datetime-local"
                            name="expires_at"
                            label="Expires At"
                            :value="old('expires_at', $banner->expires_at?->format('Y-m-d\TH:i'))"
                            helper="Leave empty for permanent banner."
                        />
                    </div>
                </x-admin.card>

                <!-- Status & Actions -->
                <x-admin.card title="Activation & Actions" icon="toggle-left">
                    <div class="space-y-4">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Active Banner</span>
                                <span class="text-[11px] text-slate-400 block">Display to customers</span>
                            </div>
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $banner->is_active) ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300"
                            />
                        </label>

                        <div class="pt-2 flex items-center gap-3">
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
                                :href="route('admin.banners.index')"
                                variant="outline"
                                size="md"
                            >
                                Cancel
                            </x-admin.button>
                        </div>

                        <div class="pt-2">
                            <button
                                type="button"
                                onclick="confirmBannerDelete({{ $banner->id }}, '{{ addslashes($banner->title) }}')"
                                class="w-full px-4 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                <span>Delete Banner</span>
                            </button>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>

    <!-- Delete Banner Form (Hidden) -->
    <form id="delete-banner-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    function previewBannerGraphic(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('banner-preview-img').src = e.target.result;
                const ph = document.getElementById('banner-placeholder');
                if (ph) ph.classList.add('hidden');
                document.getElementById('banner-preview-box').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function confirmBannerDelete(id, title) {
        if (window.Admin && window.Admin.confirm) {
            Admin.confirm({
                title: `Delete Banner?`,
                text: `Are you sure you want to delete "${title}"? This action cannot be undone.`,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc2626',
                onConfirm: () => {
                    const form = document.getElementById('delete-banner-form');
                    form.action = `/admin/banners/${id}`;
                    form.submit();
                }
            });
        }
    }
</script>
@endpush
