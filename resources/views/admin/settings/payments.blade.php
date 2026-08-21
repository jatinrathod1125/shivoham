@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="System Settings"
        subtitle="Manage store identity, currency configurations, payment gateways, and inventory policies."
        :breadcrumbs="[
            ['title' => 'Settings', 'url' => route('admin.settings.index')],
            ['title' => 'Payment Gateways', 'url' => '']
        ]"
    />

    <!-- Settings Navigation Tabs -->
    <x-admin.settings-nav active="payments" />

    <!-- Payment Gateways Form -->
    <form method="POST" action="{{ route('admin.settings.update-payments') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Payment Gateways -->
            <div class="lg:col-span-8 space-y-6">
                <!-- 1. Cash on Delivery (COD) -->
                <x-admin.card title="Cash on Delivery (COD)" subtitle="Allow customers to pay physical cash upon grocery dropoff" icon="banknote">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Enable Cash on Delivery</span>
                                <span class="text-[11px] text-slate-400 block">Offer COD option during checkout</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="cod_enabled"
                                    value="1"
                                    {{ old('cod_enabled', $settings['cod_enabled']) ? 'checked' : '' }}
                                    class="sr-only peer"
                                />
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="cod_min_amount"
                                label="Minimum Order for COD ($)"
                                placeholder="0.00"
                                :value="old('cod_min_amount', $settings['cod_min_amount'])"
                            />

                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="cod_max_amount"
                                label="Maximum Order for COD ($)"
                                placeholder="300.00"
                                :value="old('cod_max_amount', $settings['cod_max_amount'])"
                                helper="Caps cash risk on large orders."
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- 2. Stripe Payments -->
                <x-admin.card title="Stripe (Credit / Debit Cards & Apple Pay)" subtitle="Accept Visa, Mastercard, Amex, Apple Pay and Google Pay" icon="credit-card">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Enable Stripe Gateway</span>
                                <span class="text-[11px] text-slate-400 block">Instant secure online card checkout</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="stripe_enabled"
                                    value="1"
                                    {{ old('stripe_enabled', $settings['stripe_enabled']) ? 'checked' : '' }}
                                    class="sr-only peer"
                                />
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <div>
                            <x-form.select
                                name="stripe_mode"
                                label="Stripe Environment Mode"
                                :required="true"
                            >
                                <option value="test" {{ old('stripe_mode', $settings['stripe_mode']) === 'test' ? 'selected' : '' }}>Test Sandbox Mode (Testing)</option>
                                <option value="live" {{ old('stripe_mode', $settings['stripe_mode']) === 'live' ? 'selected' : '' }}>Live Production Mode (Real Transactions)</option>
                            </x-form.select>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <x-form.input
                                name="stripe_publishable_key"
                                label="Stripe Publishable Key"
                                placeholder="pk_test_..."
                                :value="old('stripe_publishable_key', $settings['stripe_publishable_key'])"
                            />

                            <x-form.input
                                type="password"
                                name="stripe_secret_key"
                                label="Stripe Secret API Key"
                                placeholder="sk_test_..."
                                :value="old('stripe_secret_key', $settings['stripe_secret_key'])"
                            />
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Save -->
            <div class="lg:col-span-4 space-y-6">
                <x-admin.card title="Save Changes" icon="check-circle-2">
                    <p class="text-xs text-slate-500 mb-4">Payment gateway credentials are encrypted in the application database settings storage.</p>

                    <x-admin.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="check"
                        class="w-full"
                    >
                        Save Payment Settings
                    </x-admin.button>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection
