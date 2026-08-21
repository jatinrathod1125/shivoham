@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="System Settings"
        subtitle="Manage store identity, currency, taxation rules, delivery zones, and payment gateways."
        :breadcrumbs="[
            ['title' => 'Settings', 'url' => route('admin.settings.index')],
            ['title' => 'Tax & Pricing', 'url' => '']
        ]"
    />

    <!-- Settings Navigation Tabs -->
    <x-admin.settings-nav active="tax" />

    <!-- Tax Form -->
    <form method="POST" action="{{ route('admin.settings.update-tax') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Tax Rules -->
            <div class="lg:col-span-8 space-y-6">
                <x-admin.card title="Taxation Calculation & Rates" subtitle="Configure sales tax rate, inclusive/exclusive pricing, and VAT display" icon="calculator">
                    <div class="space-y-4">
                        <!-- Enable Tax Switch -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Enable Automated Tax Calculation</span>
                                <span class="text-[11px] text-slate-400 block">Calculate and collect taxes during checkout</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="enable_tax"
                                    value="1"
                                    {{ old('enable_tax', $settings['enable_tax']) ? 'checked' : '' }}
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
                                max="100"
                                name="tax_rate_percentage"
                                label="Standard Tax Rate (%)"
                                placeholder="8.00"
                                :required="true"
                                :value="old('tax_rate_percentage', $settings['tax_rate_percentage'])"
                                helper="e.g. 8.00 for 8% sales tax."
                            />

                            <div>
                                <x-form.select
                                    name="tax_calculation_type"
                                    label="Tax Pricing Display Mode"
                                    :required="true"
                                >
                                    <option value="exclusive" {{ old('tax_calculation_type', $settings['tax_calculation_type']) === 'exclusive' ? 'selected' : '' }}>Exclusive (Tax is added on top at checkout)</option>
                                    <option value="inclusive" {{ old('tax_calculation_type', $settings['tax_calculation_type']) === 'inclusive' ? 'selected' : '' }}>Inclusive (Product prices already include tax)</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form.input
                                name="tax_label"
                                label="Tax Line Label"
                                placeholder="Sales Tax (GST/VAT)"
                                :required="true"
                                :value="old('tax_label', $settings['tax_label'])"
                                helper="Displayed on customer invoices."
                            />

                            <x-form.input
                                name="tax_number"
                                label="Business Tax / VAT Number"
                                placeholder="e.g. US-TAX-982341-GH"
                                :value="old('tax_number', $settings['tax_number'])"
                                helper="Printed on printable receipts & invoices."
                            />
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Save -->
            <div class="lg:col-span-4 space-y-6">
                <x-admin.card title="Save Changes" icon="check-circle-2">
                    <p class="text-xs text-slate-500 mb-4">Tax rate adjustments will apply automatically to all newly placed orders and cart totals.</p>

                    <x-admin.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="check"
                        class="w-full"
                    >
                        Save Tax Settings
                    </x-admin.button>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection
