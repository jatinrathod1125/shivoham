@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Edit Offer: {{ $offer->title }}"
        subtitle="Update discount parameters, validity dates, and campaign graphics."
        :breadcrumbs="[
            ['title' => 'Offers', 'url' => route('admin.offers.index')],
            ['title' => 'Edit Offer', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.offers.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Back to Offers
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.offers.update', $offer) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Main Campaign Details -->
            <div class="lg:col-span-8 space-y-6">
                <x-admin.card title="Campaign Information" subtitle="Offer headline and discount structure" icon="tag">
                    <div class="space-y-4">
                        <x-form.input
                            id="offer-title"
                            name="title"
                            label="Offer Title"
                            placeholder="e.g. Weekend Organic Harvest Sale"
                            :required="true"
                            :value="old('title', $offer->title)"
                            helper="Main marketing headline shown to shoppers."
                        />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form.input
                                id="offer-slug"
                                name="slug"
                                label="Campaign Slug"
                                placeholder="weekend-organic-harvest-sale"
                                :value="old('slug', $offer->slug)"
                                helper="URL identifier. Auto-generated if left blank."
                            />

                            <x-form.input
                                name="badge_text"
                                label="Badge Tag (Optional)"
                                placeholder="e.g. HOT DEAL, 20% OFF, LIMITED TIME"
                                :value="old('badge_text', $offer->badge_text)"
                                helper="Eye-catching promotional tag."
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.select
                                    name="discount_type"
                                    label="Discount Structure"
                                    :required="true"
                                >
                                    <option value="percentage" {{ old('discount_type', $offer->discount_type) === 'percentage' ? 'selected' : '' }}>Percentage (%) Discount</option>
                                    <option value="fixed" {{ old('discount_type', $offer->discount_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount ($) Off</option>
                                </x-form.select>
                            </div>

                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="discount_value"
                                label="Discount Amount / Value"
                                placeholder="e.g. 20 for 20% or 5.00 for $5"
                                :required="true"
                                :value="old('discount_value', $offer->discount_value)"
                            />
                        </div>

                        <div>
                            <x-form.textarea
                                name="description"
                                label="Offer Description / Terms"
                                placeholder="Detailed promotional summary, terms, eligible products, or restrictions..."
                                :value="old('description', $offer->description)"
                                :rows="4"
                            />
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Schedule, Banner & Status -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Validity Schedule -->
                <x-admin.card title="Campaign Schedule" subtitle="Start and expiration window" icon="calendar">
                    <div class="space-y-4">
                        <x-form.input
                            type="datetime-local"
                            name="starts_at"
                            label="Starts At"
                            :value="old('starts_at', $offer->starts_at?->format('Y-m-d\TH:i'))"
                            helper="Leave empty for immediate activation."
                        />

                        <x-form.input
                            type="datetime-local"
                            name="expires_at"
                            label="Expires At"
                            :value="old('expires_at', $offer->expires_at?->format('Y-m-d\TH:i'))"
                            helper="Leave empty for no expiration."
                        />
                    </div>
                </x-admin.card>

                <!-- Banner Media -->
                <x-admin.card title="Campaign Banner" subtitle="Promotional banner graphic" icon="image">
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
                                onchange="previewBanner(this)"
                            />
                            @if($offer->image)
                                <div id="banner-placeholder" class="hidden space-y-1.5 py-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-emerald-100 text-slate-500 group-hover:text-emerald-600 flex items-center justify-center mx-auto transition-colors">
                                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-700">Click to upload banner</p>
                                </div>
                                <div id="banner-preview-box">
                                    <img id="banner-preview-img" src="{{ $offer->image }}" alt="{{ $offer->title }}" class="w-full h-28 object-cover rounded-lg border border-slate-200" />
                                    <p class="text-[10px] text-emerald-600 font-semibold mt-2">Click to replace banner</p>
                                </div>
                            @else
                                <div id="banner-placeholder" class="space-y-1.5 py-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-emerald-100 text-slate-500 group-hover:text-emerald-600 flex items-center justify-center mx-auto transition-colors">
                                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-700">Click to upload banner</p>
                                    <p class="text-[10px] text-slate-400">PNG, JPG, WebP up to 2MB</p>
                                </div>
                                <div id="banner-preview-box" class="hidden">
                                    <img id="banner-preview-img" src="" alt="Banner Preview" class="w-full h-28 object-cover rounded-lg border border-slate-200" />
                                    <p class="text-[10px] text-emerald-600 font-semibold mt-2">Click to replace banner</p>
                                </div>
                            @endif
                        </div>
                        @error('image')
                            <p class="text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </x-admin.card>

                <!-- Status & Actions -->
                <x-admin.card title="Activation & Actions" icon="toggle-left">
                    <div class="space-y-4">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Active Campaign</span>
                                <span class="text-[11px] text-slate-400 block">Offer is visible in store</span>
                            </div>
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $offer->is_active) ? 'checked' : '' }}
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
                                :href="route('admin.offers.index')"
                                variant="outline"
                                size="md"
                            >
                                Cancel
                            </x-admin.button>
                        </div>

                        <div class="pt-2">
                            <button
                                type="button"
                                onclick="confirmOfferDelete({{ $offer->id }}, '{{ addslashes($offer->title) }}')"
                                class="w-full px-4 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                <span>Delete Offer</span>
                            </button>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>

    <!-- Delete Offer Form (Hidden) -->
    <form id="delete-offer-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    function previewBanner(input) {
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

    function confirmOfferDelete(id, title) {
        if (window.Admin && window.Admin.confirm) {
            Admin.confirm({
                title: `Delete Offer?`,
                text: `Are you sure you want to delete "${title}"? This action cannot be undone.`,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc2626',
                onConfirm: () => {
                    const form = document.getElementById('delete-offer-form');
                    form.action = `/admin/offers/${id}`;
                    form.submit();
                }
            });
        }
    }
</script>
@endpush
