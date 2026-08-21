@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Add New Customer"
        subtitle="Create a customer shopper account with primary delivery address."
        :breadcrumbs="[
            ['title' => 'Customers', 'url' => route('admin.customers.index')],
            ['title' => 'Add Customer', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.customers.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Back to Customers
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.customers.store') }}">
        @csrf

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
                            :value="old('name')"
                        />

                        <x-form.input
                            type="email"
                            name="email"
                            label="Email Address"
                            placeholder="e.g. eleanor.vance@example.com"
                            :required="true"
                            :value="old('email')"
                            icon="mail"
                        />

                        <x-form.input
                            type="tel"
                            name="phone"
                            label="Phone Number"
                            placeholder="e.g. +1 (555) 345-6789"
                            :value="old('phone')"
                            icon="phone"
                        />

                        <div>
                            <x-form.select
                                name="status"
                                label="Account Status"
                                helper="Active customers can place online orders and use discounts."
                            >
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
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
                            :value="old('address_line1')"
                        />

                        <x-form.input
                            name="address_line2"
                            label="Apt / Suite / Unit (Optional)"
                            placeholder="e.g. Apt 4B"
                            :value="old('address_line2')"
                        />

                        <div class="grid grid-cols-2 gap-3">
                            <x-form.input
                                name="city"
                                label="City"
                                placeholder="Springfield"
                                :value="old('city')"
                            />

                            <x-form.input
                                name="state"
                                label="State / Province"
                                placeholder="IL"
                                :value="old('state')"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <x-form.input
                                name="postal_code"
                                label="Postal / ZIP Code"
                                placeholder="62704"
                                :value="old('postal_code')"
                            />

                            <x-form.input
                                name="country"
                                label="Country"
                                placeholder="US"
                                :value="old('country', 'US')"
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Form Action Buttons -->
                <div class="flex items-center gap-3">
                    <x-admin.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="check"
                        class="flex-1"
                    >
                        Create Customer
                    </x-admin.button>

                    <x-admin.button
                        :href="route('admin.customers.index')"
                        variant="outline"
                        size="md"
                    >
                        Cancel
                    </x-admin.button>
                </div>
            </div>
        </div>
    </form>
@endsection
