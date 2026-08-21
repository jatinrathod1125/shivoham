@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="System Settings"
        subtitle="Manage store identity, currency configurations, delivery schedules, and payment gateways."
        :breadcrumbs="[
            ['title' => 'Settings', 'url' => route('admin.settings.index')],
            ['title' => 'Currency', 'url' => '']
        ]"
    />

    <!-- Settings Navigation Tabs -->
    <x-admin.settings-nav active="localization" />

    <!-- Localization Form -->
    <form method="POST" action="{{ route('admin.settings.update-localization') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Currency Parameters -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Currency Parameters -->
                <x-admin.card title="Currency Configuration" subtitle="Default store trading currency, symbol, and price formats" icon="coins">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <x-form.input
                                name="currency_code"
                                label="Currency Code (ISO)"
                                placeholder="USD"
                                :required="true"
                                :value="old('currency_code', $settings['currency_code'])"
                                helper="e.g. USD, EUR, GBP, CAD"
                            />

                            <x-form.input
                                name="currency_symbol"
                                label="Currency Symbol"
                                placeholder="$"
                                :required="true"
                                :value="old('currency_symbol', $settings['currency_symbol'])"
                                helper="e.g. $, €, £, ₹"
                            />

                            <div>
                                <x-form.select
                                    name="currency_position"
                                    label="Symbol Position"
                                    :required="true"
                                >
                                    <option value="left" {{ old('currency_position', $settings['currency_position']) === 'left' ? 'selected' : '' }}>Left ($100.00)</option>
                                    <option value="right" {{ old('currency_position', $settings['currency_position']) === 'right' ? 'selected' : '' }}>Right (100.00$)</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <x-form.input
                                name="decimal_separator"
                                label="Decimal Separator"
                                placeholder="."
                                :required="true"
                                :value="old('decimal_separator', $settings['decimal_separator'])"
                                helper="Default: dot (.)"
                            />

                            <x-form.input
                                name="thousands_separator"
                                label="Thousands Separator"
                                placeholder=","
                                :value="old('thousands_separator', $settings['thousands_separator'])"
                                helper="Default: comma (,)"
                            />
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Save -->
            <div class="lg:col-span-4 space-y-6">
                <x-admin.card title="Save Changes" icon="check-circle-2">
                    <p class="text-xs text-slate-500 mb-4">Currency updates automatically apply to all catalog prices, order totals, and financial reports.</p>

                    <x-admin.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="check"
                        class="w-full"
                    >
                        Save Currency Settings
                    </x-admin.button>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection
