<header class="h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6">
    <!-- Left: Mobile Toggle & Global Search -->
    <div class="flex items-center gap-3 sm:gap-4 flex-1 max-w-xl">
        <!-- Mobile Sidebar Hamburger -->
        <button
            type="button"
            id="open-mobile-sidebar-btn"
            onclick="if(window.Admin && typeof window.Admin.openMobileSidebar === 'function'){ window.Admin.openMobileSidebar(); }"
            class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:outline-hidden cursor-pointer"
            aria-label="Open sidebar"
        >
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <!-- Global Search Input -->
        <div class="relative w-full max-w-md hidden sm:block">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
            <input
                type="text"
                id="global-topbar-search"
                placeholder="Search orders, products, customers..."
                class="w-full pl-9 pr-14 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
            />
            <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
                <kbd class="inline-flex items-center rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-slate-400">Ctrl K</kbd>
            </div>
        </div>
    </div>

    <!-- Right: Actions & User Profile -->
    <div class="flex items-center gap-2 sm:gap-3">
        <!-- Fullscreen Button -->
        <button
            type="button"
            id="toggle-fullscreen-btn"
            onclick="if(window.Admin && typeof window.Admin.toggleFullscreen === 'function'){ window.Admin.toggleFullscreen(); }"
            class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
            title="Toggle Fullscreen"
        >
            <i data-lucide="maximize" class="w-4 h-4"></i>
        </button>

        <!-- Notifications Dropdown -->
        <x-admin.dropdown align="right" width="64">
            <x-slot:trigger>
                <button type="button" class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors relative cursor-pointer" aria-label="Notifications">
                    <i data-lucide="bell" class="w-4 h-4"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                </button>
            </x-slot:trigger>

            <x-slot:content>
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="text-xs font-semibold text-slate-900">Notifications</h4>
                    <span class="text-[10px] px-1.5 py-0.5 bg-emerald-50 text-emerald-700 rounded-full font-medium">3 New</span>
                </div>
                <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.orders.index') ? route('admin.orders.index') : url('/admin/orders') }}" class="flex items-start gap-3 p-3 hover:bg-slate-50 transition-colors text-left block">
                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-slate-800 truncate">New Order #ORD-1092</p>
                            <p class="text-[11px] text-slate-500">2 min ago • $48.50</p>
                        </div>
                    </a>
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.inventory.index') ? route('admin.inventory.index') : url('/admin/inventory') }}" class="flex items-start gap-3 p-3 hover:bg-slate-50 transition-colors text-left block">
                        <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-slate-800 truncate">Low Stock Alert</p>
                            <p class="text-[11px] text-slate-500">Fresh Milk 1L below threshold</p>
                        </div>
                    </a>
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.customers.index') ? route('admin.customers.index') : url('/admin/customers') }}" class="flex items-start gap-3 p-3 hover:bg-slate-50 transition-colors text-left block">
                        <div class="w-8 h-8 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-slate-800 truncate">New Customer Registered</p>
                            <p class="text-[11px] text-slate-500">Sarah Jenkins joined</p>
                        </div>
                    </a>
                </div>
                <div class="p-2 border-t border-slate-100 text-center">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.orders.index') ? route('admin.orders.index') : url('/admin/orders') }}" class="text-[11px] font-medium text-emerald-600 hover:text-emerald-700">View All Notifications</a>
                </div>
            </x-slot:content>
        </x-admin.dropdown>

        <div class="h-5 w-px bg-slate-200 mx-0.5"></div>

        <!-- User Profile Dropdown -->
        <x-admin.dropdown align="right" width="56">
            <x-slot:trigger>
                <div class="flex items-center gap-2.5 p-1 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                    <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-semibold text-xs flex items-center justify-center shadow-xs">
                        {{ strtoupper(substr(auth()->user()->name ?? 'Admin', 0, 2)) }}
                    </div>
                    <div class="text-left hidden md:block">
                        <div class="text-xs font-semibold text-slate-800 leading-tight truncate max-w-[120px]">
                            {{ auth()->user()->name ?? 'Admin User' }}
                        </div>
                        <div class="text-[10px] text-slate-500 leading-tight">
                            {{ auth()->user()->role ?? 'Super Admin' }}
                        </div>
                    </div>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 hidden md:block"></i>
                </div>
            </x-slot:trigger>

            <x-slot:content>
                <div class="px-4 py-2.5 border-b border-slate-100">
                    <p class="text-xs font-semibold text-slate-900 truncate">{{ auth()->user()->name ?? 'Admin User' }}</p>
                    <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email ?? 'admin@grocery.local' }}</p>
                </div>

                <div class="py-1">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.profile') ? route('admin.profile') : url('/admin/profile') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition-colors">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                        <span>Admin Profile</span>
                    </a>
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.settings.index') ? route('admin.settings.index') : url('/admin/settings') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i>
                        <span>Settings</span>
                    </a>
                </div>

                <div class="border-t border-slate-100 py-1">
                    @if(\Illuminate\Support\Facades\Route::has('admin.logout'))
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer">
                                <i data-lucide="log-out" class="w-4 h-4 text-rose-500"></i>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ url('/admin/logout') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4 text-rose-500"></i>
                            <span>Sign Out</span>
                        </a>
                    @endif
                </div>
            </x-slot:content>
        </x-admin.dropdown>
    </div>
</header>
