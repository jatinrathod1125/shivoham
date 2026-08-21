@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Offers & Deals"
        subtitle="Manage grocery discounts, seasonal campaigns, promotional badges, and flash deal countdowns."
        :breadcrumbs="[
            ['title' => 'Offers & Deals', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.offers.create')"
                variant="primary"
                size="sm"
                icon="plus"
            >
                Add Offer
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- Marketing Module Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-1">
        <a
            href="{{ route('admin.offers.index') }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-emerald-600 text-white shadow-xs"
        >
            <i data-lucide="percent" class="w-4 h-4"></i>
            <span>Promotional Offers & Deals</span>
        </a>

        <a
            href="{{ route('admin.coupons.index') }}"
            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/80 transition-all flex items-center gap-2"
        >
            <i data-lucide="ticket" class="w-4 h-4 text-slate-400"></i>
            <span>Coupon Promo Codes</span>
        </a>
    </div>

    <!-- KPI Metric Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="percent" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Campaigns</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="flame" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active Deals</p>
                <p class="text-xl font-bold text-emerald-600 mt-0.5">{{ number_format($stats['active']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <i data-lucide="calendar" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Upcoming</p>
                <p class="text-xl font-bold text-sky-600 mt-0.5">{{ number_format($stats['upcoming']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Expired</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['expired']) }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.offers.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
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
                        placeholder="Search campaign title, badge text, description..."
                        class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                    />
                </div>
            </div>

            <!-- Discount Type Filter -->
            <div class="lg:col-span-3">
                <select
                    name="discount_type"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                >
                    <option value="">All Discount Types</option>
                    <option value="percentage" {{ request('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%) Off</option>
                    <option value="fixed" {{ request('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount ($) Off</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                >
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive Only</option>
                </select>

                @if(request()->hasAny(['search', 'discount_type', 'status']))
                    <a
                        href="{{ route('admin.offers.index') }}"
                        class="p-2 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Clear filters"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.card>

    <!-- Offers Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Promotional Campaign</th>
                        <th class="px-5 py-3">Discount</th>
                        <th class="px-5 py-3">Validity Schedule</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($offers as $offer)
                        <tr class="hover:bg-slate-50/70 transition-colors" id="offer-row-{{ $offer->id }}">
                            <!-- Campaign Details -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-10 rounded-xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                                        @if($offer->image)
                                            <img src="{{ $offer->image }}" alt="{{ $offer->title }}" class="w-full h-full object-cover" />
                                        @else
                                            <i data-lucide="tag" class="w-5 h-5"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.offers.edit', $offer) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition-colors truncate">
                                                {{ $offer->title }}
                                            </a>
                                            @if($offer->badge_text)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase">
                                                    {{ $offer->badge_text }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 truncate max-w-[280px] mt-0.5">{{ $offer->description }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Discount Value -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 text-xs font-bold border border-purple-100">
                                    {{ $offer->discount_type === 'percentage' ? ((float)$offer->discount_value) . '% OFF' : '$' . number_format($offer->discount_value, 2) . ' OFF' }}
                                </span>
                            </td>

                            <!-- Validity Window -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="text-xs text-slate-700 font-medium">
                                    {{ $offer->starts_at?->format('M d, Y') ?? 'Immediate' }}
                                    <span class="text-slate-400 mx-1">➔</span>
                                    {{ $offer->expires_at?->format('M d, Y') ?? 'Never Expires' }}
                                </div>
                                <div class="text-[11px] mt-0.5">
                                    @if($offer->is_active && (!$offer->expires_at || $offer->expires_at->isFuture()))
                                        <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Active Campaign
                                        </span>
                                    @elseif($offer->expires_at && $offer->expires_at->isPast())
                                        <span class="text-slate-400">Expired</span>
                                    @else
                                        <span class="text-amber-600">Inactive</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        {{ $offer->is_active ? 'checked' : '' }}
                                        onchange="toggleOfferStatus({{ $offer->id }}, this)"
                                        class="sr-only peer"
                                    />
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.offers.edit', $offer) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                        title="Edit Offer"
                                    >
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <button
                                        type="button"
                                        onclick="confirmOfferDelete({{ $offer->id }}, '{{ addslashes($offer->title) }}')"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Delete Offer"
                                    >
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <x-admin.empty-state
                                    title="No Offers Found"
                                    description="No promotional campaigns match your filters. Create a new campaign to boost sales."
                                    icon="percent"
                                    actionText="Add New Offer"
                                    :actionUrl="route('admin.offers.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($offers->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $offers->links() }}
            </div>
        @endif
    </x-admin.card>

    <!-- Delete Offer Form (Hidden) -->
    <form id="delete-offer-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    // AJAX Status Toggle
    function toggleOfferStatus(id, checkbox) {
        fetch(`/admin/offers/${id}/toggle-status`, {
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
    function confirmOfferDelete(id, title) {
        if (window.Admin && window.Admin.confirm) {
            Admin.confirm({
                title: `Delete Offer?`,
                text: `Are you sure you want to delete "${title}"? This action cannot be undone.`,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc2626',
                onConfirm: () => {
                    const form = document.getElementById('delete-offer-form');
                    form.action = `/admin/offers/${id}`;
                    form.submit();
                }
            });
        }
    }
</script>
@endpush
