<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    @include('partials.head', ['title' => $title ?? config('admin.name', 'Grocery Admin')])
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-900 bg-slate-100 flex overflow-hidden">
    <!-- Desktop Sidebar (Sticky Dark Navigation) -->
    <div class="hidden lg:flex lg:shrink-0">
        <x-admin.sidebar />
    </div>

    <!-- Mobile Sidebar Drawer (Off-canvas) -->
    <div id="mobile-sidebar-drawer" class="relative z-50 lg:hidden hidden" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div
            id="mobile-sidebar-backdrop"
            onclick="if(window.Admin && typeof window.Admin.closeMobileSidebar === 'function'){ window.Admin.closeMobileSidebar(); }"
            class="fixed inset-0 bg-slate-900/80 backdrop-blur-xs transition-opacity ease-linear duration-300 opacity-0 cursor-pointer"
        ></div>

        <div class="fixed inset-0 flex">
            <!-- Off-canvas menu container -->
            <div id="mobile-sidebar-content" class="relative mr-16 flex w-full max-w-xs flex-1 transform transition ease-in-out duration-300 -translate-x-full">
                <!-- Close Button -->
                <div class="absolute top-0 right-0 -mr-12 pt-4">
                    <button
                        type="button"
                        id="close-mobile-sidebar-btn"
                        onclick="if(window.Admin && typeof window.Admin.closeMobileSidebar === 'function'){ window.Admin.closeMobileSidebar(); }"
                        class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-hidden focus:ring-2 focus:ring-inset focus:ring-white text-white hover:bg-white/10 cursor-pointer"
                    >
                        <span class="sr-only">Close sidebar</span>
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Mobile Sidebar Content -->
                <x-admin.sidebar class="w-full h-full" />
            </div>

            <div class="w-14 shrink-0" aria-hidden="true">
                <!-- Force sidebar to shrink to fit close icon -->
            </div>
        </div>
    </div>

    <!-- Main Content Area (Full Available Width) -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden w-full">
        <!-- Topbar -->
        <x-admin.topbar />

        <!-- Main Body Scroll Area (Full Width with Comfortable Responsive Padding) -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-100/70">
            <div class="w-full space-y-6">
                <!-- Flash messages -->
                @include('partials.flash')

                <!-- Page Content -->
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Global Scripts -->
    @include('partials.scripts')
    @stack('scripts')
</body>
</html>
