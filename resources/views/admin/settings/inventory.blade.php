@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="System Settings"
        subtitle="Manage store identity, currency, taxation rules, delivery zones, and payment gateways."
        :breadcrumbs="[
            ['title' => 'Settings', 'url' => route('admin.settings.index')],
            ['title' => 'Stock & Alerts', 'url' => '']
        ]"
    />

    <!-- Settings Navigation Tabs -->
    <x-admin.settings-nav active="inventory" />

    <!-- Stock & Inventory Alerts Form -->
    <form method="POST" action="{{ route('admin.settings.update-inventory') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Inventory Policy & Alerts -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Stock Level Policies -->
                <x-admin.card title="Inventory Thresholds & Visibility" subtitle="Control out-of-stock display rules and default reorder levels" icon="boxes">
                    <div class="space-y-4">
                        <x-form.input
                            type="number"
                            min="1"
                            max="1000"
                            name="default_low_stock_threshold"
                            label="Default Catalog Low-Stock Threshold (Units)"
                            placeholder="10"
                            :required="true"
                            :value="old('default_low_stock_threshold', $settings['default_low_stock_threshold'])"
                            helper="Products with inventory below this quantity trigger low-stock alerts."
                        />

                        <!-- Visibility Switches -->
                        <div class="space-y-3 pt-2">
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Hide Out of Stock Products</span>
                                    <span class="text-[11px] text-slate-400 block">Automatically hide catalog items with 0 stock from customer catalog</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="hide_out_of_stock"
                                        value="1"
                                        {{ old('hide_out_of_stock', $settings['hide_out_of_stock']) ? 'checked' : '' }}
                                        class="sr-only peer"
                                    />
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Allow Backorders</span>
                                    <span class="text-[11px] text-slate-400 block">Allow customers to order items even if stock quantity is depleted</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="allow_backorders"
                                        value="1"
                                        {{ old('allow_backorders', $settings['allow_backorders']) ? 'checked' : '' }}
                                        class="sr-only peer"
                                    />
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </x-admin.card>

                <!-- Automated Alert Notifications -->
                <x-admin.card title="Automated Stock Email Notifications" subtitle="Send email alerts to purchasing manager when products run low" icon="bell">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Enable Automated Low Stock Email Alerts</span>
                                <span class="text-[11px] text-slate-400 block">Dispatches email summary when an order depletes inventory below threshold</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="enable_stock_alert_emails"
                                    value="1"
                                    {{ old('enable_stock_alert_emails', $settings['enable_stock_alert_emails']) ? 'checked' : '' }}
                                    class="sr-only peer"
                                />
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <div>
                            <x-form.input
                                type="email"
                                name="stock_alert_email"
                                label="Inventory Alert Recipient Email"
                                placeholder="inventory@grocery.local"
                                :value="old('stock_alert_email', $settings['stock_alert_email'])"
                                helper="Email address that receives low-stock threshold alerts."
                            />
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Save -->
            <div class="lg:col-span-4 space-y-6">
                <x-admin.card title="Save Changes" icon="check-circle-2">
                    <p class="text-xs text-slate-500 mb-4">Stock thresholds determine inventory health badges across catalog management tables.</p>

                    <x-admin.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="check"
                        class="w-full"
                    >
                        Save Stock Policies
                    </x-admin.button>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection
