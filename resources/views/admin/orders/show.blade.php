@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Order #{{ $order->order_number }}"
        subtitle="Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }} • {{ $order->created_at->diffForHumans() }}"
        :breadcrumbs="[
            ['title' => 'Orders', 'url' => route('admin.orders.index')],
            ['title' => '#' . $order->order_number, 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.orders.invoice', $order)"
                target="_blank"
                variant="primary"
                size="sm"
                icon="printer"
            >
                Print Invoice
            </x-admin.button>

            <x-admin.button
                :href="route('admin.orders.index')"
                variant="outline"
                size="sm"
                icon="arrow-left"
            >
                Back to Orders
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Order Items & Financials -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Line Items Card -->
            <x-admin.card title="Purchased Grocery Items" subtitle="Ordered items and itemized costs" icon="shopping-bag">
                <div class="overflow-x-auto -mx-5 -my-5">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                            <tr>
                                <th class="px-5 py-3">Item</th>
                                <th class="px-5 py-3 text-center">Unit Price</th>
                                <th class="px-5 py-3 text-center">Qty</th>
                                <th class="px-5 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <!-- Item Name & Thumbnail -->
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 text-slate-500 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                                                @if($item->product?->thumbnail)
                                                    <img src="{{ $item->product->thumbnail }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover" />
                                                @else
                                                    <i data-lucide="package" class="w-5 h-5 text-slate-400"></i>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                @if($item->product)
                                                    <a href="{{ route('admin.products.edit', $item->product) }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors truncate block max-w-[220px]">
                                                        {{ $item->product_name }}
                                                    </a>
                                                @else
                                                    <span class="font-semibold text-slate-900 truncate block max-w-[220px]">{{ $item->product_name }}</span>
                                                @endif
                                                <span class="text-[11px] text-slate-400 font-mono">{{ $item->sku }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Unit Price -->
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        ${{ number_format($item->unit_price, 2) }}
                                    </td>

                                    <!-- Quantity -->
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-bold text-slate-800 text-xs">
                                            {{ $item->quantity }}
                                        </span>
                                    </td>

                                    <!-- Line Total -->
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap font-bold text-slate-900">
                                        ${{ number_format($item->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Financial Calculation Breakdown -->
                <div class="pt-5 mt-5 border-t border-slate-100 flex justify-end">
                    <div class="w-full sm:w-72 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Items Subtotal</span>
                            <span class="font-semibold text-slate-900">${{ number_format($order->subtotal, 2) }}</span>
                        </div>

                        @if($order->discount > 0)
                            <div class="flex items-center justify-between text-rose-600">
                                <span>Promotional Discount</span>
                                <span class="font-semibold">-${{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between text-slate-600">
                            <span>Delivery / Shipping Fee</span>
                            <span class="font-semibold text-slate-900">
                                {{ $order->shipping_fee > 0 ? '$' . number_format($order->shipping_fee, 2) : 'FREE' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-slate-600">
                            <span>Estimated Tax (8%)</span>
                            <span class="font-semibold text-slate-900">${{ number_format($order->tax, 2) }}</span>
                        </div>

                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-sm font-bold text-slate-900">
                            <span>Grand Total</span>
                            <span class="text-emerald-600 text-base">${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </x-admin.card>

            <!-- Delivery Instructions / Order Notes -->
            @if($order->notes)
                <x-admin.card title="Customer Delivery Notes" icon="message-square">
                    <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-900 leading-relaxed">
                        {{ $order->notes }}
                    </div>
                </x-admin.card>
            @endif
        </div>

        <!-- Right Column: Status Controls & Customer Sidebar -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Status & Workflow Control -->
            <x-admin.card title="Order Status & Workflow" icon="settings">
                <form id="order-status-update-form" onsubmit="handleStatusFormSubmit(event)" class="space-y-4">
                    <!-- Fulfillment Status -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Fulfillment Status</label>
                        <select
                            id="order-status-select"
                            name="status"
                            class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold uppercase focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                        >
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing / Packing</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered / Completed</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Payment Status -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Payment Status</label>
                        <select
                            id="order-payment-status-select"
                            name="payment_status"
                            class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold uppercase focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                        >
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <x-admin.button type="submit" variant="primary" size="sm" icon="check" class="w-full">
                        Update Status
                    </x-admin.button>
                </form>
            </x-admin.card>

            <!-- Customer Summary Card -->
            <x-admin.card title="Customer Details" icon="user">
                <div class="space-y-3">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($order->customer_name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            @if($order->customer)
                                <a href="{{ route('admin.customers.show', $order->customer) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition-colors truncate block">
                                    {{ $order->customer_name }}
                                </a>
                            @else
                                <span class="font-bold text-slate-900 truncate block">{{ $order->customer_name }}</span>
                            @endif
                            <span class="text-[11px] text-slate-400 truncate block">{{ $order->customer_email }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs text-slate-700">
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                            <span>{{ $order->customer_phone ?: 'No phone recorded' }}</span>
                        </div>
                    </div>
                </div>
            </x-admin.card>

            <!-- Delivery Address Card -->
            <x-admin.card title="Shipping Address" icon="map-pin">
                <div class="text-xs text-slate-700 leading-relaxed">
                    {{ $order->shipping_address ?: 'No shipping address specified.' }}
                </div>
            </x-admin.card>

            <!-- Payment Summary Card -->
            <x-admin.card title="Payment & Logistics" icon="credit-card">
                <div class="space-y-2.5 text-xs text-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Method</span>
                        <span class="font-semibold text-slate-900">{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Delivered At</span>
                        <span class="font-semibold text-slate-900">{{ $order->delivered_at?->format('M d, Y h:i A') ?? 'Not yet delivered' }}</span>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function handleStatusFormSubmit(e) {
        e.preventDefault();
        const status = $('#order-status-select').val();
        const paymentStatus = $('#order-payment-status-select').val();
        const token = $('meta[name="csrf-token"]').attr('content');

        const req1 = $.ajax({
            url: "{{ route('admin.orders.update-status', $order) }}",
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': token },
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ status: status })
        });

        const req2 = $.ajax({
            url: "{{ route('admin.orders.update-payment-status', $order) }}",
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': token },
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ payment_status: paymentStatus })
        });

        $.when(req1, req2).done(function(statusRes, paymentRes) {
            const data1 = statusRes[0];
            const data2 = paymentRes[0];
            if (data1.success && data2.success) {
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'success', title: 'Order Updated', message: 'Order status and payment updated successfully.' });
                }
                setTimeout(() => window.location.reload(), 600);
            }
        }).fail(function() {
            if (window.Admin && window.Admin.toast) {
                Admin.toast({ type: 'error', title: 'Server Error', message: 'Could not update order status.' });
            }
        });
    }
</script>
@endpush
