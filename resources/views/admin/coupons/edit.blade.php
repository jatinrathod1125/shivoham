@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Edit Coupon: {{ $coupon->code }}"
        subtitle="Update discount values, redemption limits, and usage criteria."
        :breadcrumbs="[
            ['title' => 'Coupons', 'url' => route('admin.coupons.index')],
            ['title' => 'Edit Coupon', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.coupons.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Back to Coupons
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Code & Discount Rules -->
            <div class="lg:col-span-8 space-y-6">
                <x-admin.card title="Coupon Parameters" subtitle="Promo code name, discount formula and cart conditions" icon="ticket">
                    <div class="space-y-4">
                        <!-- Code Input -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Coupon Promo Code <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="coupon-code-input"
                                name="code"
                                value="{{ old('code', $coupon->code) }}"
                                placeholder="e.g. ORGANIC20"
                                required
                                class="w-full px-3.5 py-2 text-xs font-mono font-bold uppercase tracking-wider bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                            />
                            @error('code')
                                <p class="text-[11px] text-rose-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.select
                                    name="type"
                                    label="Discount Type"
                                    :required="true"
                                >
                                    <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Percentage (%) Discount</option>
                                    <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount ($) Off</option>
                                </x-form.select>
                            </div>

                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="value"
                                label="Discount Amount / Value"
                                placeholder="e.g. 20 for 20% or 10.00 for $10"
                                :required="true"
                                :value="old('value', $coupon->value)"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="min_spend"
                                label="Minimum Cart Spend Requirement ($)"
                                placeholder="0.00"
                                :value="old('min_spend', $coupon->min_spend)"
                                helper="Minimum total order value required."
                            />

                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="max_discount"
                                label="Max Discount Limit ($) (Optional)"
                                placeholder="e.g. 50.00"
                                :value="old('max_discount', $coupon->max_discount)"
                            />
                        </div>

                        <div>
                            <x-form.textarea
                                name="description"
                                label="Description / Customer Terms"
                                placeholder="e.g. Enjoy 20% discount on orders over $50."
                                :value="old('description', $coupon->description)"
                                :rows="3"
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Usage Analytics Card -->
                <x-admin.card title="Coupon Usage Analytics" icon="zap">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase">Times Redeemed</span>
                            <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($coupon->usage_count) }} times</p>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase">Global Limit</span>
                            <p class="text-xl font-bold text-slate-900 mt-1">{{ $coupon->usage_limit ? number_format($coupon->usage_limit) : 'Unlimited' }}</p>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Limits, Schedule & Status -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Usage Limits -->
                <x-admin.card title="Redemption Limits" subtitle="Cap usage counts" icon="sliders">
                    <div class="space-y-4">
                        <x-form.input
                            type="number"
                            min="1"
                            name="usage_limit"
                            label="Total Redemption Limit (Global)"
                            placeholder="Leave empty for unlimited"
                            :value="old('usage_limit', $coupon->usage_limit)"
                            helper="Total times this coupon can be redeemed across all customers."
                        />

                        <x-form.input
                            type="number"
                            min="1"
                            name="per_user_limit"
                            label="Max Uses Per Customer"
                            placeholder="e.g. 1"
                            :value="old('per_user_limit', $coupon->per_user_limit)"
                            helper="Number of times an individual customer can use this code."
                        />
                    </div>
                </x-admin.card>

                <!-- Validity Schedule -->
                <x-admin.card title="Validity Window" subtitle="Start and expiration dates" icon="calendar">
                    <div class="space-y-4">
                        <x-form.input
                            type="datetime-local"
                            name="starts_at"
                            label="Starts At"
                            :value="old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i'))"
                            helper="Leave empty for immediate activation."
                        />

                        <x-form.input
                            type="datetime-local"
                            name="expires_at"
                            label="Expires At"
                            :value="old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i'))"
                            helper="Leave empty for no expiration."
                        />
                    </div>
                </x-admin.card>

                <!-- Status & Actions -->
                <x-admin.card title="Activation & Actions" icon="toggle-left">
                    <div class="space-y-4">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Active Coupon</span>
                                <span class="text-[11px] text-slate-400 block">Allow shoppers to redeem at checkout</span>
                            </div>
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}
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
                                :href="route('admin.coupons.index')"
                                variant="outline"
                                size="md"
                            >
                                Cancel
                            </x-admin.button>
                        </div>

                        <div class="pt-2">
                            <button
                                type="button"
                                onclick="confirmCouponDelete({{ $coupon->id }}, '{{ addslashes($coupon->code) }}')"
                                class="w-full px-4 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                <span>Delete Coupon</span>
                            </button>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>

    <!-- Delete Coupon Form (Hidden) -->
    <form id="delete-coupon-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    function confirmCouponDelete(id, code) {
        if (window.Admin && window.Admin.confirm) {
            Admin.confirm({
                title: `Delete Coupon?`,
                text: `Are you sure you want to delete coupon "${code}"? This action cannot be undone.`,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc2626',
                onConfirm: () => {
                    $('#delete-coupon-form').attr('action', `/admin/coupons/${id}`).trigger('submit');
                }
            });
        }
    }
</script>
@endpush
