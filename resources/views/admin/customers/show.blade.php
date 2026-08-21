@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="{{ $customer->name }}"
        subtitle="Shopper profile, order history, lifetime value, and delivery addresses."
        :breadcrumbs="[
            ['title' => 'Customers', 'url' => route('admin.customers.index')],
            ['title' => $customer->name, 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.customers.edit', $customer)"
                variant="primary"
                size="sm"
                icon="edit-3"
            >
                Edit Profile
            </x-admin.button>

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

    <!-- Customer Lifetime KPI Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="dollar-sign" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Lifetime Spend</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">${{ number_format($totalSpent, 2) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Orders</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $ordersCount }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Avg Order Value</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">${{ number_format($aov, 2) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="calendar" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Shopper Since</p>
                <p class="text-xs font-bold text-slate-900 mt-1">{{ $customer->created_at?->format('M d, Y') ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Personal Info & Saved Addresses -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Customer Card -->
            <x-admin.card title="Personal Profile" icon="user">
                <div class="space-y-3.5">
                    <div class="flex items-center gap-3 pb-3.5 border-b border-slate-100">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-800 font-bold text-sm flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}{{ strtoupper(substr(strstr($customer->name, ' ') ?: ' ', 1, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-slate-900 truncate">{{ $customer->name }}</h4>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold uppercase mt-0.5 {{ $customer->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ $customer->status }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs text-slate-700">
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <a href="mailto:{{ $customer->email }}" class="text-emerald-600 hover:underline truncate">{{ $customer->email }}</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <span>{{ $customer->phone ?: 'No phone provided' }}</span>
                        </div>
                    </div>
                </div>
            </x-admin.card>

            <!-- Delivery Addresses -->
            <x-admin.card title="Saved Delivery Addresses" subtitle="Registered physical destinations" icon="map-pin">
                <div class="space-y-3">
                    @forelse($customer->addresses as $address)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-800 uppercase tracking-wider">{{ $address->type ?: 'Address' }}</span>
                                @if($address->is_default)
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                        Default Shipping
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-700">{{ $address->address_line1 }}</p>
                            @if($address->address_line2)
                                <p class="text-xs text-slate-500">{{ $address->address_line2 }}</p>
                            @endif
                            <p class="text-[11px] text-slate-500">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}, {{ $address->country }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic py-2">No delivery addresses recorded.</p>
                    @endforelse
                </div>
            </x-admin.card>
        </div>

        <!-- Right Column: Order History -->
        <div class="lg:col-span-8 space-y-6">
            <x-admin.card title="Customer Order History" subtitle="Chronological record of all grocery checkouts and order deliveries" icon="shopping-cart">
                <div class="overflow-x-auto -mx-5 -my-5">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                            <tr>
                                <th class="px-5 py-3">Order Number</th>
                                <th class="px-5 py-3">Date</th>
                                <th class="px-5 py-3 text-center">Items</th>
                                <th class="px-5 py-3">Total</th>
                                <th class="px-5 py-3">Payment</th>
                                <th class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($customer->orders as $order)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <!-- Order Number -->
                                    <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-slate-900">
                                        {{ $order->order_number }}
                                    </td>

                                    <!-- Date -->
                                    <td class="px-5 py-3.5 whitespace-nowrap text-slate-500">
                                        {{ $order->created_at->format('M d, Y h:i A') }}
                                    </td>

                                    <!-- Items Count -->
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 font-semibold text-slate-800 text-[11px]">
                                            {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                        </span>
                                    </td>

                                    <!-- Total -->
                                    <td class="px-5 py-3.5 whitespace-nowrap font-bold text-slate-900">
                                        ${{ number_format($order->total, 2) }}
                                    </td>

                                    <!-- Payment Status -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            {{ $order->payment_status }}
                                        </span>
                                    </td>

                                    <!-- Order Status -->
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase {{ $order->status === 'delivered' ? 'bg-emerald-50 text-emerald-700' : ($order->status === 'processing' ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-700') }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <x-admin.empty-state
                                            title="No Orders Placed"
                                            description="This customer has not placed any grocery orders yet."
                                            icon="shopping-cart"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        </div>
    </div>
@endsection
