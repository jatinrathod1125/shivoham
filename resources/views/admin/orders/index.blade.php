@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Orders"
        subtitle="Manage grocery customer sales, order fulfillment, delivery statuses, and payment tracking."
        :breadcrumbs="[
            ['title' => 'Orders', 'url' => '']
        ]"
    >
    </x-admin.page-header>

    <!-- KPI Metric Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Orders</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pending Orders</p>
                <p class="text-xl font-bold text-amber-600 mt-0.5">{{ number_format($stats['pending']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Delivered</p>
                <p class="text-xl font-bold text-emerald-600 mt-0.5">{{ number_format($stats['delivered']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="dollar-sign" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Paid Revenue</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">${{ number_format($stats['total_revenue'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Status Navigation Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200">
        <a
            href="{{ route('admin.orders.index', array_merge(request()->except('status'), ['status' => ''])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ !request('status') ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>All Orders</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ !request('status') ? 'bg-emerald-700/60 text-white' : 'bg-slate-100 text-slate-600' }}">
                {{ $stats['total'] }}
            </span>
        </a>

        <a
            href="{{ route('admin.orders.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ request('status') === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>Pending</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ request('status') === 'pending' ? 'bg-amber-700/60 text-white' : 'bg-amber-50 text-amber-700' }}">
                {{ $stats['pending'] }}
            </span>
        </a>

        <a
            href="{{ route('admin.orders.index', array_merge(request()->except('status'), ['status' => 'processing'])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ request('status') === 'processing' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>Processing</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ request('status') === 'processing' ? 'bg-sky-700/60 text-white' : 'bg-sky-50 text-sky-700' }}">
                {{ $stats['processing'] }}
            </span>
        </a>

        <a
            href="{{ route('admin.orders.index', array_merge(request()->except('status'), ['status' => 'delivered'])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ request('status') === 'delivered' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>Delivered</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ request('status') === 'delivered' ? 'bg-emerald-700/60 text-white' : 'bg-emerald-50 text-emerald-700' }}">
                {{ $stats['delivered'] }}
            </span>
        </a>

        <a
            href="{{ route('admin.orders.index', array_merge(request()->except('status'), ['status' => 'cancelled'])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ request('status') === 'cancelled' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>Cancelled</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ request('status') === 'cancelled' ? 'bg-rose-700/60 text-white' : 'bg-rose-50 text-rose-700' }}">
                {{ $stats['cancelled'] }}
            </span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}" />
            @endif

            <!-- Search Input -->
            <div class="lg:col-span-5">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search order number, customer name, email, phone..."
                        class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                    />
                </div>
            </div>

            <!-- Payment Status Filter -->
            <div class="lg:col-span-3">
                <select
                    name="payment_status"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Payment Statuses</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <!-- Payment Method Filter -->
            <div class="lg:col-span-2">
                <select
                    name="payment_method"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Methods</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                    <option value="apple_pay" {{ request('payment_method') === 'apple_pay' ? 'selected' : '' }}>Apple Pay</option>
                    <option value="cash_on_delivery" {{ request('payment_method') === 'cash_on_delivery' ? 'selected' : '' }}>COD</option>
                </select>
            </div>

            <!-- Sort Filter -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                >
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Latest First</option>
                    <option value="total_desc" {{ request('sort') === 'total_desc' ? 'selected' : '' }}>Total: High-Low</option>
                    <option value="total_asc" {{ request('sort') === 'total_asc' ? 'selected' : '' }}>Total: Low-High</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                </select>

                @if(request()->hasAny(['search', 'payment_status', 'payment_method', 'sort']))
                    <a
                        href="{{ route('admin.orders.index', ['status' => request('status')]) }}"
                        class="p-2 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Clear filters"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.card>

    <!-- Orders Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Order Number</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Items Summary</th>
                        <th class="px-5 py-3">Total Amount</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3 text-center">Fulfillment Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/70 transition-colors" id="order-row-{{ $order->id }}">
                            <!-- Order Number & Timestamp -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-xs text-slate-900 hover:text-emerald-600 transition-colors block">
                                    {{ $order->order_number }}
                                </a>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $order->created_at->diffForHumans() }} ({{ $order->created_at->format('M d, H:i') }})
                                </div>
                            </td>

                            <!-- Customer Profile -->
                            <td class="px-5 py-3.5">
                                <div class="min-w-0">
                                    @if($order->customer)
                                        <a href="{{ route('admin.customers.show', $order->customer) }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors truncate block max-w-[180px]">
                                            {{ $order->customer_name }}
                                        </a>
                                    @else
                                        <span class="font-semibold text-slate-900 truncate block max-w-[180px]">{{ $order->customer_name }}</span>
                                    @endif
                                    <div class="text-[11px] text-slate-400 truncate max-w-[180px]">{{ $order->customer_email }}</div>
                                </div>
                            </td>

                            <!-- Items Summary -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 font-bold text-slate-800 text-[11px] shrink-0">
                                        {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                    </span>
                                    <span class="text-slate-500 text-[11px] truncate max-w-[180px] block">
                                        {{ $order->items->pluck('product_name')->take(2)->join(', ') }}{{ $order->items->count() > 2 ? '...' : '' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Total Amount -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-sm text-slate-900">${{ number_format($order->total, 2) }}</div>
                                @if($order->discount > 0)
                                    <div class="text-[10px] text-rose-600">-${{ number_format($order->discount, 2) }} discount</div>
                                @endif
                            </td>

                            <!-- Payment Method & Status -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($order->payment_status === 'refunded' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                        {{ $order->payment_status }}
                                    </span>
                                    <div class="text-[11px] text-slate-400 font-medium">
                                        {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}
                                    </div>
                                </div>
                            </td>

                            <!-- Fulfillment Status (Interactive AJAX selector) -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <select
                                    onchange="updateOrderStatus({{ $order->id }}, this.value, this)"
                                    class="py-1 px-2.5 rounded-lg text-xs font-bold uppercase transition-colors border cursor-pointer {{ $order->status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($order->status === 'processing' ? 'bg-sky-50 text-sky-700 border-sky-200' : ($order->status === 'cancelled' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200')) }}"
                                >
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.orders.show', $order) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors inline-flex items-center gap-1 text-xs font-semibold"
                                        title="View Order Details & Invoice"
                                    >
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                        <span>Details</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <x-admin.empty-state
                                    title="No Orders Found"
                                    description="No customer sales orders match the active status tab or filter query."
                                    icon="shopping-bag"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </x-admin.card>
@endsection

@push('scripts')
<script>
    // AJAX Order Fulfillment Status update with jQuery
    function updateOrderStatus(id, newStatus, selectElement) {
        const $select = $(selectElement);
        $.ajax({
            url: `/admin/orders/${id}/update-status`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ status: newStatus }),
            success: function(data) {
                if (data.success) {
                    // Update select element colors
                    const colorClass = (newStatus === 'delivered' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                        (newStatus === 'processing' ? 'bg-sky-50 text-sky-700 border-sky-200' :
                        (newStatus === 'cancelled' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200')));

                    $select.attr('class', 'py-1 px-2.5 rounded-lg text-xs font-bold uppercase transition-colors border cursor-pointer ' + colorClass);

                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'success', title: 'Order Updated', message: data.message });
                    }
                } else {
                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to update order status.' });
                    }
                }
            },
            error: function() {
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'error', title: 'Server Error', message: 'Could not connect to server.' });
                }
            }
        });
    }
</script>
@endpush
