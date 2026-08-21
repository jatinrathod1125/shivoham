@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="System Settings"
        subtitle="Manage store identity, currency, taxation rules, delivery zones, and payment gateways."
        :breadcrumbs="[
            ['title' => 'Settings', 'url' => route('admin.settings.index')],
            ['title' => 'Shipping & Delivery Slots', 'url' => '']
        ]"
    />

    <!-- Settings Navigation Tabs -->
    <x-admin.settings-nav active="shipping" />

    <!-- Shipping & Delivery Slots Form -->
    <form method="POST" action="{{ route('admin.settings.update-shipping') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Shipping Rates & Slots -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Shipping Rates & Free Threshold -->
                <x-admin.card title="Delivery Fees & Free Shipping Rules" subtitle="Automated threshold calculations for grocery checkout" icon="truck">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="free_shipping_threshold"
                                label="Free Delivery Minimum ($)"
                                placeholder="40.00"
                                :required="true"
                                :value="old('free_shipping_threshold', $settings['free_shipping_threshold'])"
                                helper="Orders above this amount get free delivery."
                            />

                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="default_shipping_fee"
                                label="Standard Delivery Fee ($)"
                                placeholder="4.99"
                                :required="true"
                                :value="old('default_shipping_fee', $settings['default_shipping_fee'])"
                                helper="Standard 2-4 hour grocery delivery."
                            />

                            <x-form.input
                                type="number"
                                step="0.01"
                                min="0"
                                name="express_shipping_fee"
                                label="Express Priority Fee ($)"
                                placeholder="9.99"
                                :required="true"
                                :value="old('express_shipping_fee', $settings['express_shipping_fee'])"
                                helper="Super-fast 45-minute delivery."
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Delivery Slots Manager -->
                <x-admin.card title="Delivery Time Window Slots" subtitle="Available daily delivery windows customer can select at checkout" icon="clock">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-2 border-b border-slate-100">
                            <x-form.input
                                type="number"
                                min="1"
                                max="500"
                                name="max_orders_per_slot"
                                label="Max Orders Allowed Per Slot Window"
                                placeholder="25"
                                :required="true"
                                :value="old('max_orders_per_slot', $settings['max_orders_per_slot'])"
                                helper="Prevents fleet courier overload."
                            />

                            <x-form.input
                                type="number"
                                min="0"
                                max="1440"
                                name="slot_cutoff_minutes"
                                label="Order Cutoff Buffer (Minutes)"
                                placeholder="60"
                                :required="true"
                                :value="old('slot_cutoff_minutes', $settings['slot_cutoff_minutes'])"
                                helper="e.g. 60 min before slot start."
                            />
                        </div>

                        <!-- Dynamic Slots List -->
                        <div class="space-y-2.5">
                            <label class="block text-xs font-semibold text-slate-700">Active Delivery Windows</label>
                            
                            <div id="slots-container" class="space-y-2">
                                @foreach($settings['delivery_slots'] as $index => $slot)
                                    <div class="flex items-center gap-2 slot-row">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                            <i data-lucide="clock-3" class="w-4 h-4"></i>
                                        </div>
                                        <input
                                            type="text"
                                            name="delivery_slots[]"
                                            value="{{ $slot }}"
                                            placeholder="e.g. 08:00 - 11:00"
                                            required
                                            class="flex-1 px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500"
                                        />
                                        <button
                                            type="button"
                                            onclick="removeSlotRow(this)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Delete slot"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <button
                                type="button"
                                onclick="addSlotRow()"
                                class="mt-2 px-3 py-1.5 rounded-lg border border-dashed border-slate-300 hover:border-emerald-500 hover:text-emerald-700 text-slate-600 text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer"
                            >
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Add Delivery Window Slot</span>
                            </button>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Save -->
            <div class="lg:col-span-4 space-y-6">
                <x-admin.card title="Save Changes" icon="check-circle-2">
                    <p class="text-xs text-slate-500 mb-4">Delivery slots and shipping fees update immediately for all shopping carts at checkout.</p>

                    <x-admin.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="check"
                        class="w-full"
                    >
                        Save Shipping Settings
                    </x-admin.button>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function addSlotRow() {
        const container = document.getElementById('slots-container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 slot-row';
        div.innerHTML = `
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                <i data-lucide="clock-3" class="w-4 h-4"></i>
            </div>
            <input
                type="text"
                name="delivery_slots[]"
                value="17:00 - 20:00"
                placeholder="e.g. 17:00 - 20:00"
                required
                class="flex-1 px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500"
            />
            <button
                type="button"
                onclick="removeSlotRow(this)"
                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                title="Delete slot"
            >
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(div);
        if (window.Admin && typeof window.Admin.refreshIcons === 'function') {
            window.Admin.refreshIcons();
        }
    }

    function removeSlotRow(btn) {
        const rows = document.querySelectorAll('.slot-row');
        if (rows.length > 1) {
            btn.closest('.slot-row').remove();
        } else {
            if (window.Admin && window.Admin.toast) {
                Admin.toast({ type: 'warning', title: 'Action Denied', message: 'You must maintain at least 1 active delivery window.' });
            }
        }
    }
</script>
@endpush
