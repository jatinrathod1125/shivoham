@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Customers"
        subtitle="Manage grocery shopper accounts, delivery addresses, order histories, and customer lifetime value."
        :breadcrumbs="[
            ['title' => 'Customers', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.customers.create')"
                variant="primary"
                size="sm"
                icon="user-plus"
            >
                Add Customer
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- KPI Metric Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Customers</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active Shoppers</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['active']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="dollar-sign" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Customer Revenue</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">${{ number_format($stats['total_spent'], 2) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Average LTV</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">${{ number_format($stats['avg_ltv'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.customers.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
            <!-- Search Input -->
            <div class="lg:col-span-6">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search customer name, email address, phone..."
                        class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                    />
                </div>
            </div>

            <!-- Status Filter -->
            <div class="lg:col-span-3">
                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Customer Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>

            <!-- Sort Filter -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                >
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                    <option value="spent_desc" {{ request('sort') === 'spent_desc' ? 'selected' : '' }}>Top Spending (LTV)</option>
                    <option value="orders_desc" {{ request('sort') === 'orders_desc' ? 'selected' : '' }}>Most Orders</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                </select>

                @if(request()->hasAny(['search', 'status', 'sort']))
                    <a
                        href="{{ route('admin.customers.index') }}"
                        class="p-2 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Clear filters"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.card>

    <!-- Customers Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3">Primary Delivery Address</th>
                        <th class="px-5 py-3 text-center">Orders</th>
                        <th class="px-5 py-3">Total Spent</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/70 transition-colors" id="customer-row-{{ $customer->id }}">
                            <!-- Customer Info -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}{{ strtoupper(substr(strstr($customer->name, ' ') ?: ' ', 1, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.customers.show', $customer) }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors truncate block max-w-[180px]">
                                            {{ $customer->name }}
                                        </a>
                                        <div class="text-[11px] text-slate-400 truncate max-w-[180px]">{{ $customer->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Phone -->
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-600 font-mono text-[11px]">
                                {{ $customer->phone ?: 'N/A' }}
                            </td>

                            <!-- Default Address -->
                            <td class="px-5 py-3.5">
                                @php $defAddr = $customer->addresses->first(); @endphp
                                @if($defAddr)
                                    <div class="text-xs text-slate-800 truncate max-w-[220px]">
                                        {{ $defAddr->address_line1 }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[220px]">
                                        {{ $defAddr->city }}, {{ $defAddr->state }} {{ $defAddr->postal_code }}
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">No address saved</span>
                                @endif
                            </td>

                            <!-- Orders Count -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 text-xs font-semibold">
                                    {{ $customer->total_orders_count }} {{ Str::plural('order', $customer->total_orders_count) }}
                                </span>
                            </td>

                            <!-- Total Spent -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-xs text-slate-900">${{ number_format($customer->total_spent, 2) }}</span>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        {{ $customer->status === 'active' ? 'checked' : '' }}
                                        onchange="toggleCustomerStatus({{ $customer->id }}, this)"
                                        class="sr-only peer"
                                    />
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.customers.show', $customer) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                        title="View Profile & Orders"
                                    >
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>

                                    <a
                                        href="{{ route('admin.customers.edit', $customer) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                        title="Edit Customer"
                                    >
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <button
                                        type="button"
                                        onclick="confirmCustomerDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}', {{ $customer->orders()->count() }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Delete Customer"
                                    >
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <x-admin.empty-state
                                    title="No Customers Found"
                                    description="No registered shopper profiles match your search criteria."
                                    icon="users"
                                    actionText="Add New Customer"
                                    :actionUrl="route('admin.customers.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </x-admin.card>

    <!-- Delete Customer Form (Hidden) -->
    <form id="delete-customer-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    // AJAX Status Toggle
    function toggleCustomerStatus(id, checkbox) {
        const url = `/admin/customers/${id}/toggle-status`;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'success', title: 'Status Updated', message: data.message });
                }
            } else {
                checkbox.checked = !checkbox.checked;
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to update status.' });
                }
            }
        })
        .catch(err => {
            checkbox.checked = !checkbox.checked;
            if (window.Admin && window.Admin.toast) {
                Admin.toast({ type: 'error', title: 'Server Error', message: 'Could not connect to server.' });
            }
        });
    }

    // SweetAlert2 Delete Confirmation
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
                    const form = document.getElementById('delete-customer-form');
                    form.action = `/admin/customers/${id}`;
                    form.submit();
                }
            });
        }
    }
</script>
@endpush
