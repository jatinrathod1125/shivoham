@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $currentPath = request()->path();
    $storeLogo =
        \App\Models\Setting::get('store_logo') ?? (config('admin.logo.dark_sidebar') ?? config('admin.logo.full'));
    $storeName = \App\Models\Setting::get('store_name') ?? config('admin.name', 'Grocery Admin');

    $navGroups = [
        [
            'title' => 'Main',
            'items' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.dashboard')
                        ? route('admin.dashboard')
                        : url('/admin/dashboard'),
                    'active' =>
                        request()->is('admin') ||
                        request()->is('admin/dashboard') ||
                        request()->is('design-system') ||
                        request()->is('/'),
                ],
            ],
        ],
        [
            'title' => 'Catalog',
            'items' => [
                [
                    'title' => 'Products',
                    'icon' => 'package',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.products.index')
                        ? route('admin.products.index')
                        : url('/admin/products'),
                    'active' => request()->is('admin/products*'),
                ],
                [
                    'title' => 'Categories',
                    'icon' => 'layers',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.categories.index')
                        ? route('admin.categories.index')
                        : url('/admin/categories'),
                    'active' => request()->is('admin/categories*'),
                ],
                [
                    'title' => 'Brands',
                    'icon' => 'tag',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.brands.index')
                        ? route('admin.brands.index')
                        : url('/admin/brands'),
                    'active' => request()->is('admin/brands*'),
                ],
            ],
        ],
        [
            'title' => 'Sales & Customers',
            'items' => [
                [
                    'title' => 'Orders',
                    'icon' => 'shopping-bag',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.orders.index')
                        ? route('admin.orders.index')
                        : url('/admin/orders'),
                    'active' => request()->is('admin/orders*'),
                    'badge' => 'New',
                    'badgeVariant' => 'emerald',
                ],
                [
                    'title' => 'Customers',
                    'icon' => 'users',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.customers.index')
                        ? route('admin.customers.index')
                        : url('/admin/customers'),
                    'active' => request()->is('admin/customers*'),
                ],
            ],
        ],
        [
            'title' => 'Inventory',
            'items' => [
                [
                    'title' => 'Inventory',
                    'icon' => 'boxes',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.inventory.index')
                        ? route('admin.inventory.index')
                        : url('/admin/inventory'),
                    'active' => request()->is('admin/inventory*'),
                ],
            ],
        ],
        [
            'title' => 'Marketing',
            'items' => [
                [
                    'title' => 'Offers & Deals',
                    'icon' => 'percent',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.offers.index')
                        ? route('admin.offers.index')
                        : url('/admin/offers'),
                    'active' => request()->is('admin/offers*'),
                ],
                [
                    'title' => 'Coupons',
                    'icon' => 'ticket',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.coupons.index')
                        ? route('admin.coupons.index')
                        : url('/admin/coupons'),
                    'active' => request()->is('admin/coupons*'),
                ],
                [
                    'title' => 'Banners',
                    'icon' => 'image',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.banners.index')
                        ? route('admin.banners.index')
                        : url('/admin/banners'),
                    'active' => request()->is('admin/banners*'),
                ],
            ],
        ],
        [
            'title' => 'Analytics',
            'items' => [
                [
                    'title' => 'Reports',
                    'icon' => 'bar-chart-3',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.reports.index')
                        ? route('admin.reports.index')
                        : url('/admin/reports'),
                    'active' => request()->is('admin/reports*'),
                ],
            ],
        ],
        [
            'title' => 'System',
            'items' => [
                [
                    'title' => 'Settings',
                    'icon' => 'settings',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.settings.index')
                        ? route('admin.settings.index')
                        : url('/admin/settings'),
                    'active' => request()->is('admin/settings*'),
                ],
            ],
        ],
    ];
@endphp

<aside
    {{ $attributes->merge(['class' => 'w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 border-r border-slate-800 select-none h-screen sticky top-0']) }}>
    <!-- Brand Header -->
    <div class="py-5 px-4 flex items-center justify-center bg-white border-b border-slate-200/90 shadow-xs">
        <a href="{{ \Illuminate\Support\Facades\Route::has('admin.dashboard') ? route('admin.dashboard') : url('/') }}"
            class="flex items-center justify-center w-full group">
            @if ($storeLogo)
                <img src="{{ $storeLogo }}" alt="{{ $storeName }}"
                    class="h-16 max-h-20 max-w-[210px] w-auto object-contain transition-transform duration-200 group-hover:scale-105 mx-auto" />
            @else
                <div class="flex flex-col items-center justify-center gap-2 text-center">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-900/30 group-hover:bg-emerald-500 transition-colors">
                        <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-900 tracking-tight">{{ $storeName }}</span>
                </div>
            @endif
        </a>
    </div>

    <!-- Navigation List -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        @foreach ($navGroups as $group)
            <div class="space-y-1">
                <div class="px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    {{ $group['title'] }}
                </div>

                @foreach ($group['items'] as $item)
                    <a href="{{ $item['url'] }}"
                        class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-all duration-150 group {{ $item['active'] ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-900/20 font-semibold' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="{{ $item['icon'] }}"
                                class="w-4 h-4 {{ $item['active'] ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
                            <span>{{ $item['title'] }}</span>
                        </div>

                        @if (isset($item['badge']))
                            <span
                                class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $item['active'] ? 'bg-white text-emerald-800' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>

    <!-- Bottom Store Status Widget -->
    <div class="p-3 border-t border-slate-800 bg-slate-950/30">
        <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-2.5 w-2.5">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <div>
                    <div class="text-[11px] font-semibold text-white">Store Online</div>
                    <div class="text-[10px] text-slate-400">All services active</div>
                </div>
            </div>
            <a href="{{ \Illuminate\Support\Facades\Route::has('admin.settings.index') ? route('admin.settings.index') : url('/admin/settings') }}"
                class="text-slate-400 hover:text-white p-1 rounded-md transition-colors" title="Settings">
                <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
</aside>
