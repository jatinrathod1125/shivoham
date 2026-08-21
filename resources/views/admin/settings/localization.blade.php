@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="System Settings"
        subtitle="Manage store identity, currency, taxation rules, delivery zones, and payment gateways."
        :breadcrumbs="[
            ['title' => 'Settings', 'url' => route('admin.settings.index')],
            ['title' => 'Localization & Currency', 'url' => '']
        ]"
    />

    <!-- Settings Navigation Tabs -->
    <x-admin.settings-nav active="localization" />

    <!-- Localization Form -->
    <form method="POST" action="{{ route('admin.settings.update-localization') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Currency & Formats -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Currency Parameters -->
                <x-admin.card title="Currency Configuration" subtitle="Default store trading currency and price symbols" icon="coins">
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

                <!-- Timezone & Date Formats -->
                <x-admin.card title="Date & Timezone Formats" subtitle="Timestamps and scheduling intervals" icon="calendar">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-form.select
                                    name="timezone"
                                    label="Server Timezone"
                                    :required="true"
                                >
                                    <option value="UTC" {{ old('timezone', $settings['timezone']) === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                    <option value="America/New_York" {{ old('timezone', $settings['timezone']) === 'America/New_York' ? 'selected' : '' }}>America/New York (EST/EDT)</option>
                                    <option value="America/Chicago" {{ old('timezone', $settings['timezone']) === 'America/Chicago' ? 'selected' : '' }}>America/Chicago (CST/CDT)</option>
                                    <option value="America/Los_Angeles" {{ old('timezone', $settings['timezone']) === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los Angeles (PST/PDT)</option>
                                    <option value="Europe/London" {{ old('timezone', $settings['timezone']) === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT/BST)</option>
                                    <option value="Asia/Kolkata" {{ old('timezone', $settings['timezone']) === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                                </x-form.select>
                            </div>

                            <div>
                                <x-form.select
                                    name="date_format"
                                    label="Date Display Format"
                                    :required="true"
                                >
                                    <option value="M d, Y" {{ old('date_format', $settings['date_format']) === 'M d, Y' ? 'selected' : '' }}>Aug 21, 2026</option>
                                    <option value="Y-m-d" {{ old('date_format', $settings['date_format']) === 'Y-m-d' ? 'selected' : '' }}>2026-08-21 (ISO)</option>
                                    <option value="d/m/Y" {{ old('date_format', $settings['date_format']) === 'd/m/Y' ? 'selected' : '' }}>21/08/2026 (EU/UK)</option>
                                    <option value="m/d/Y" {{ old('date_format', $settings['date_format']) === 'm/d/Y' ? 'selected' : '' }}>08/21/2026 (US)</option>
                                </x-form.select>
                            </div>

                            <div>
                                <x-form.select
                                    name="time_format"
                                    label="Time Format"
                                    :required="true"
                                >
                                    <option value="12h" {{ old('time_format', $settings['time_format']) === '12h' ? 'selected' : '' }}>12-Hour (e.g. 02:30 PM)</option>
                                    <option value="24h" {{ old('time_format', $settings['time_format']) === '24h' ? 'selected' : '' }}>24-Hour (e.g. 14:30)</option>
                                </x-form.select>
                            </div>
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
                        Save Localization
                    </x-admin.button>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection
