@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Add New Coupon Code"
        subtitle="Create a discount coupon code with custom spend requirements and redemption limits."
        :breadcrumbs="[
            ['title' => 'Coupons', 'url' => route('admin.coupons.index')],
            ['title' => 'Add Coupon', 'url' => '']
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

    <form method="POST" action="{{ route('admin.coupons.store') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Code & Discount Rules -->
            <div class="lg:col-span-8 space-y-6">
                <x-admin.card title="Coupon Parameters" subtitle="Promo code name, discount formula and cart conditions" icon="ticket">
                    <div class="space-y-4">
                        <!-- Code Input with Random Generator -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Coupon Promo Code <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input
                                        type="text"
                                        id="coupon-code-input"
                                        name="code"
                                        value="{{ old('code') }}"
                                        placeholder="e.g. ORGANIC20"
                                        required
                                        class="w-full px-3.5 py-2 text-xs font-mono font-bold uppercase tracking-wider bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                                    />
                                </div>
                                <button
                                    type="button"
                                    onclick="generateRandomCode()"
                                    class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition-colors flex items-center gap-1.5 shrink-0 cursor-pointer"
                                >
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    <span>Generate</span>
                                </button>
                            </div>
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
                                    <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage (%) Discount</option>
                                    <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount ($) Off</option>
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
                                :value="old('value')"
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
                                :value="old('min_spend', '0.00')"
                                helper="Minimum cart value required."
                            />

                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="max_discount"
                                label="Max Discount Limit ($) (Optional)"
                                placeholder="e.g. 50.00"
                                :value="old('max_discount')"
                                helper="Caps max discount for percentage promos."
                            />
                        </div>

                        <div>
                            <x-form.textarea
                                name="description"
                                label="Description / Customer Terms"
                                placeholder="e.g. Enjoy 20% discount on orders over $50. Applies to all fresh produce items."
                                :value="old('description')"
                                :rows="3"
                            />
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
                            :value="old('usage_limit')"
                            helper="Total times this coupon can be redeemed across all customers."
                        />

                        <x-form.input
                            type="number"
                            min="1"
                            name="per_user_limit"
                            label="Max Uses Per Customer"
                            placeholder="e.g. 1"
                            :value="old('per_user_limit', '1')"
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
                            :value="old('starts_at')"
                            helper="Leave empty for immediate activation."
                        />

                        <x-form.input
                            type="datetime-local"
                            name="expires_at"
                            label="Expires At"
                            :value="old('expires_at')"
                            helper="Leave empty for no expiration."
                        />
                    </div>
                </x-admin.card>

                <!-- Status & Actions -->
                <x-admin.card title="Activation" icon="toggle-left">
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
                                {{ old('is_active', '1') ? 'checked' : '' }}
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
                                Create Coupon
                            </x-admin.button>

                            <x-admin.button
                                :href="route('admin.coupons.index')"
                                variant="outline"
                                size="md"
                            >
                                Cancel
                            </x-admin.button>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function generateRandomCode() {
        const prefixes = ['SAVE', 'FRESH', 'GROCERY', 'ORGANIC', 'DEAL', 'SUPER'];
        const randomPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
        const randomNum = Math.floor(10 + Math.random() * 90);
        document.getElementById('coupon-code-input').value = `${randomPrefix}${randomNum}`;
    }
</script>
@endpush
