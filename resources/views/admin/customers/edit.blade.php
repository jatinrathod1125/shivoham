@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Edit Customer: {{ $customer->name }}"
        subtitle="Update customer profile, contact points, and shipping address."
        :breadcrumbs="[
            ['title' => 'Customers', 'url' => route('admin.customers.index')],
            ['title' => $customer->name, 'url' => route('admin.customers.show', $customer)],
            ['title' => 'Edit', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.customers.show', $customer)"
                variant="outline"
                size="sm"
                icon="eye"
            >
                View Profile
            </x-admin.button>

            <x-admin.button
                :href="route('admin.customers.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Back to List
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Personal Information -->
            <div class="lg:col-span-7 space-y-6">
                <x-admin.card title="Personal Profile" subtitle="Account identification and contact points" icon="user">
                    <div class="space-y-4">
                        <x-form.input
                            name="name"
                            label="Full Name"
                            placeholder="e.g. Eleanor Vance"
                            :required="true"
                            :value="old('name', $customer->name)"
                        />

                        <x-form.input
                            type="email"
                            name="email"
                            label="Email Address"
                            placeholder="e.g. eleanor.vance@example.com"
                            :required="true"
                            :value="old('email', $customer->email)"
                            icon="mail"
                        />

                        <x-form.input
                            type="tel"
                            name="phone"
                            label="Phone Number"
                            placeholder="e.g. +1 (555) 345-6789"
                            :value="old('phone', $customer->phone)"
                            icon="phone"
                        />

                        <div>
                            <x-form.select
                                name="status"
                                label="Account Status"
                                helper="Active customers can place online orders and use discounts."
                            >
                                <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="blocked" {{ old('status', $customer->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </x-form.select>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Primary Delivery Address -->
            <div class="lg:col-span-5 space-y-6">
                <x-admin.card title="Primary Delivery Address" subtitle="Default grocery drop-off location" icon="map-pin">
                    <div class="space-y-4">
                        <x-form.input
                            name="address_line1"
                            label="Street Address Line 1"
                            placeholder="e.g. 742 Evergreen Terrace"
                            :value="old('address_line1', $defaultAddress?->address_line1)"
                        />

                        <x-form.input
                            name="address_line2"
                            label="Apt / Suite / Unit (Optional)"
                            placeholder="e.g. Apt 4B"
                            :value="old('address_line2', $defaultAddress?->address_line2)"
                        />

                        <div class="grid grid-cols-2 gap-3">
                            <x-form.input
                                name="city"
                                label="City"
                                placeholder="Springfield"
                                :value="old('city', $defaultAddress?->city)"
                            />

                            <x-form.input
                                name="state"
                                label="State / Province"
                                placeholder="IL"
                                :value="old('state', $defaultAddress?->state)"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <x-form.input
                                name="postal_code"
                                label="Postal / ZIP Code"
                                placeholder="62704"
                                :value="old('postal_code', $defaultAddress?->postal_code)"
                            />

                            <x-form.input
                                name="country"
                                label="Country"
                                placeholder="US"
                                :value="old('country', $defaultAddress?->country ?? 'US')"
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Form Action Buttons -->
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
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
                            :href="route('admin.customers.index')"
                            variant="outline"
                            size="md"
                        >
                            Cancel
                        </x-admin.button>
                    </div>

                    <div class="pt-2">
                        <button
                            type="button"
                            onclick="confirmCustomerDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}', {{ $customer->orders()->count() }})"
                            class="w-full px-4 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Delete Customer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Customer Form (Hidden) -->
    <form id="delete-customer-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    // SweetAlert2 Delete Confirmation with jQuery
    function confirmCustomerDelete(id, name, ordersCount) {
        if (ordersCount > 0) {
            Swal.fire({
                title: 'Cannot Delete Customer',
                text: `"${name}" has ${ordersCount} recorded order history invoice(s). To restrict access, please set status to Inactive or Blocked instead of deleting.`,
                icon: 'warning',
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'Understand'
            });
            return;
        }

        if (window.Admin && window.Admin.confirm) {
            Admin.confirm({
                title: `Delete Customer?`,
                text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc2626',
                onConfirm: () => {
                    $('#delete-customer-form').attr('action', `/admin/customers/${id}`).trigger('submit');
                }
            });
        }
    }
</script>
@endpush
