@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Promotional Banners"
        subtitle="Manage hero carousel sliders, promotional grid banners, category banners, and popup announcements."
        :breadcrumbs="[
            ['title' => 'Banners', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-admin.button
                    :href="route('admin.banners.import')"
                    variant="secondary"
                    size="sm"
                    icon="sparkles"
                >
                    Import AI Banner
                </x-admin.button>
                <x-admin.button
                    :href="route('admin.banners.create')"
                    variant="primary"
                    size="sm"
                    icon="plus"
                >
                    Add Banner
                </x-admin.button>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- KPI Metric Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="image" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Banners</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active Banners</p>
                <p class="text-xl font-bold text-emerald-600 mt-0.5">{{ number_format($stats['active']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <i data-lucide="layout-grid" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Hero Sliders</p>
                <p class="text-xl font-bold text-sky-600 mt-0.5">{{ number_format($stats['hero']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="megaphone" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Promo & Category</p>
                <p class="text-xl font-bold text-amber-600 mt-0.5">{{ number_format($stats['promo']) }}</p>
            </div>
        </div>
    </div>

    <!-- Position Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200">
        <a
            href="{{ route('admin.banners.index', array_merge(request()->except('position'), ['position' => ''])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ !request('position') ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>All Placements</span>
        </a>

        <a
            href="{{ route('admin.banners.index', array_merge(request()->except('position'), ['position' => 'home_hero'])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ request('position') === 'home_hero' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>Home Hero Slider</span>
        </a>

        <a
            href="{{ route('admin.banners.index', array_merge(request()->except('position'), ['position' => 'promotional_bar'])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ request('position') === 'promotional_bar' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>Promo Bar</span>
        </a>

        <a
            href="{{ route('admin.banners.index', array_merge(request()->except('position'), ['position' => 'category_top'])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ request('position') === 'category_top' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>Category Header</span>
        </a>

        <a
            href="{{ route('admin.banners.index', array_merge(request()->except('position'), ['position' => 'sidebar'])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ request('position') === 'sidebar' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>Sidebar</span>
        </a>

        <a
            href="{{ route('admin.banners.index', array_merge(request()->except('position'), ['position' => 'popup'])) }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2 {{ request('position') === 'popup' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}"
        >
            <span>Popup Modal</span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.banners.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
            @if(request('position'))
                <input type="hidden" name="position" value="{{ request('position') }}" />
            @endif

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
                        placeholder="Search banner headline, subtitle, target link..."
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
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <!-- Sort Filter -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500 transition-all"
                >
                    <option value="sort_asc" {{ request('sort') === 'sort_asc' ? 'selected' : '' }}>Sort: Display Priority</option>
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest Created</option>
                    <option value="title_asc" {{ request('sort') === 'title_asc' ? 'selected' : '' }}>Title: A to Z</option>
                </select>

                @if(request()->hasAny(['search', 'status', 'sort']))
                    <a
                        href="{{ route('admin.banners.index', ['position' => request('position')]) }}"
                        class="p-2 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Clear filters"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.card>

    <!-- Banners Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto -mx-5 -my-5">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Banner Graphic</th>
                        <th class="px-5 py-3">Placement Position</th>
                        <th class="px-5 py-3">Target CTA Link</th>
                        <th class="px-5 py-3 text-center">Sort Order</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($banners as $banner)
                        <tr class="hover:bg-slate-50/70 transition-colors" id="banner-row-{{ $banner->id }}">
                            <!-- Banner Info & Image -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3.5">
                                    <div
                                        onclick="openBannerPreview('{{ addslashes($banner->title) }}', '{{ addslashes($banner->subtitle ?? '') }}', '{{ $banner->image }}', '{{ $banner->position }}', '{{ $banner->link }}', '{{ $banner->isDynamicTemplate() ? route('admin.banners.preview', $banner) : '' }}')"
                                        class="w-24 h-14 rounded-xl bg-slate-900 border border-slate-200/80 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs group relative cursor-pointer"
                                        title="Click to Preview Banner Mockup"
                                    >
                                        @if($banner->isDynamicTemplate())
                                            <div class="w-full h-full relative overflow-hidden bg-slate-950 pointer-events-none">
                                                <iframe src="{{ route('admin.banners.preview', $banner) }}" class="w-[400%] h-[400%] origin-top-left transform scale-25 pointer-events-none border-0" scrolling="no" loading="lazy"></iframe>
                                            </div>
                                        @elseif($banner->image)
                                            <img src="{{ $banner->image }}" alt="{{ $banner->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                        @else
                                            <i data-lucide="image" class="w-5 h-5 text-slate-400"></i>
                                        @endif
                                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ $banner->isDynamicTemplate() ? route('admin.banners.editor', $banner) : route('admin.banners.edit', $banner) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition-colors truncate block max-w-[240px]">
                                            {{ $banner->title }}
                                        </a>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            @if($banner->isDynamicTemplate())
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-purple-50 text-purple-700 border border-purple-200 uppercase tracking-wider">
                                                    AI Dynamic
                                                </span>
                                            @endif
                                            @if($banner->subtitle)
                                                <span class="text-[11px] text-slate-400 truncate max-w-[180px]">{{ $banner->subtitle }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Position Badge -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @switch($banner->position)
                                    @case('home_hero')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                            Home Hero Slider
                                        </span>
                                        @break
                                    @case('promotional_bar')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            Promotional Bar
                                        </span>
                                        @break
                                    @case('category_top')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Category Header
                                        </span>
                                        @break
                                    @case('sidebar')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Sidebar
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700">
                                            {{ ucfirst(str_replace('_', ' ', $banner->position)) }}
                                        </span>
                                @endswitch
                            </td>

                            <!-- CTA Link -->
                            <td class="px-5 py-3.5">
                                @if($banner->link)
                                    <a href="{{ $banner->link }}" target="_blank" class="text-xs text-emerald-600 hover:underline font-mono truncate block max-w-[200px]">
                                        {{ $banner->link }}
                                    </a>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">No link attached</span>
                                @endif
                            </td>

                            <!-- Sort Order -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap font-mono font-semibold text-slate-700">
                                {{ $banner->sort_order }}
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        {{ $banner->is_active ? 'checked' : '' }}
                                        onchange="toggleBannerStatus({{ $banner->id }}, this)"
                                        class="sr-only peer"
                                    />
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        onclick="openBannerPreview('{{ addslashes($banner->title) }}', '{{ addslashes($banner->subtitle ?? '') }}', '{{ $banner->image }}', '{{ $banner->position }}', '{{ $banner->link }}', '{{ $banner->isDynamicTemplate() ? route('admin.banners.preview', $banner) : '' }}')"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-sky-50 transition-colors cursor-pointer"
                                        title="Live Storefront Preview"
                                    >
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>

                                    <a
                                        href="{{ $banner->isDynamicTemplate() ? route('admin.banners.editor', $banner) : route('admin.banners.edit', $banner) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                        title="{{ $banner->isDynamicTemplate() ? 'Edit Dynamic Content' : 'Edit Banner' }}"
                                    >
                                        <i data-lucide="{{ $banner->isDynamicTemplate() ? 'sliders' : 'edit-3' }}" class="w-4 h-4"></i>
                                    </a>

                                    <button
                                        type="button"
                                        onclick="confirmBannerDelete({{ $banner->id }}, '{{ addslashes($banner->title) }}')"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Delete Banner"
                                    >
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <x-admin.empty-state
                                    title="No Banners Found"
                                    description="No marketing banners match the active filter position."
                                    icon="image"
                                    actionText="Add New Banner"
                                    :actionUrl="route('admin.banners.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($banners->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $banners->links() }}
            </div>
        @endif
    </x-admin.card>

    <!-- Interactive Banner Storefront Mockup Preview Modal -->
    <div id="banner-preview-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200 flex flex-col max-h-[92vh]">
            <!-- Modal Header -->
            <div class="px-6 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900" id="modal-banner-title">Storefront Banner Preview</h3>
                        <p class="text-[11px] text-slate-500" id="modal-banner-position">Home Hero Slider Mockup</p>
                    </div>
                </div>

                <!-- Dynamic Viewport Switcher (Shown for AI Dynamic Banners) -->
                <div id="modal-viewport-controls" class="hidden items-center gap-1 bg-slate-200/80 p-1 rounded-xl">
                    <button type="button" onclick="setModalViewport('100%', this)" class="modal-vp-btn active px-3 py-1 text-xs font-bold rounded-lg bg-white text-slate-900 shadow-2xs">Desktop</button>
                    <button type="button" onclick="setModalViewport('768px', this)" class="modal-vp-btn px-3 py-1 text-xs font-bold rounded-lg text-slate-600 hover:text-slate-900">Tablet (768px)</button>
                    <button type="button" onclick="setModalViewport('375px', this)" class="modal-vp-btn px-3 py-1 text-xs font-bold rounded-lg text-slate-600 hover:text-slate-900">Mobile (375px)</button>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="closeBannerPreview()"
                        class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200/60 transition-colors cursor-pointer"
                    >
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-6 overflow-y-auto space-y-4 flex-1 bg-slate-50/50">
                <!-- 1. AI Dynamic Banner Live Iframe Preview (Browser Window Mockup) -->
                <div id="modal-dynamic-preview" class="hidden flex-col items-center justify-center w-full">
                    <div id="modal-iframe-wrapper" class="w-full transition-all duration-300 rounded-xl overflow-hidden bg-white border border-slate-200 shadow-xl flex flex-col">
                        <!-- Browser Window Chrome Topbar -->
                        <div class="w-full bg-slate-100 border-b border-slate-200 px-4 py-2 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-400 inline-block"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block"></span>
                            </div>
                            <div class="flex items-center gap-1.5 bg-white px-3 py-0.5 rounded-md text-[11px] font-mono text-slate-500 border border-slate-200/80 shadow-2xs max-w-sm truncate">
                                <i data-lucide="lock" class="w-3 h-3 text-emerald-600"></i>
                                <span>https://shivoham.store/</span>
                            </div>
                            <div class="w-10"></div>
                        </div>

                        <!-- Sandboxed Live Banner Iframe -->
                        <iframe id="modal-dynamic-iframe" src="about:blank" class="w-full min-h-[580px] h-[620px] border-0 bg-white" scrolling="auto" sandbox="allow-scripts allow-same-origin"></iframe>
                    </div>
                </div>

                <!-- 2. Static Banner Fallback Preview -->
                <div id="modal-static-preview" class="rounded-xl border border-slate-200 overflow-hidden shadow-inner bg-slate-950 relative group aspect-16/9 flex items-end">
                    <img id="modal-banner-image" src="" alt="Banner Preview" class="absolute inset-0 w-full h-full object-cover opacity-90 group-hover:scale-102 transition-transform duration-500" />
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>

                    <!-- Overlay Copy Content -->
                    <div class="relative z-10 p-6 sm:p-8 space-y-2 text-white max-w-xl">
                        <span id="modal-badge-position" class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-500 text-white tracking-wider shadow-xs">
                            Featured Campaign
                        </span>
                        <h2 id="modal-banner-headline" class="text-xl sm:text-2xl font-black tracking-tight leading-tight text-white drop-shadow-md">
                            Banner Headline
                        </h2>
                        <p id="modal-banner-subtitle" class="text-xs sm:text-sm text-slate-200 line-clamp-2 drop-shadow-sm">
                            Banner description subtitle goes here.
                        </p>
                        <div class="pt-2">
                            <a
                                id="modal-banner-cta"
                                href="#"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-950/40 transition-colors"
                            >
                                <span>Shop Collection</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Footer details -->
                <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-200">
                    <span id="modal-banner-link-text" class="font-mono truncate max-w-md">Target: /categories</span>
                    <button
                        type="button"
                        onclick="closeBannerPreview()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition-colors cursor-pointer"
                    >
                        Close Preview
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Banner Form (Hidden) -->
    <form id="delete-banner-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    // Listen for iframe height adjustments
    window.addEventListener('message', function(e) {
        if (e.data && e.data.type === 'banner-resize' && e.data.height) {
            const dynamicHeight = Math.min(Math.max(e.data.height, 560), 750);
            $('#modal-dynamic-iframe').css('height', dynamicHeight + 'px');
        }
    });

    // Live Storefront Banner Preview Modal with jQuery
    function openBannerPreview(title, subtitle, image, position, link, dynamicUrl) {
        $('#modal-banner-title').text(title);
        $('#modal-banner-headline').text(title);
        $('#modal-banner-subtitle').text(subtitle || 'Fresh farm-to-door grocery essentials delivered daily.');
        $('#modal-banner-position').text(position.replace('_', ' ').toUpperCase() + ' PLACEMENT');
        $('#modal-badge-position').text(position.replace('_', ' ').toUpperCase());

        if (dynamicUrl) {
            // Show AI Dynamic interactive iframe preview
            $('#modal-static-preview').addClass('hidden');
            $('#modal-dynamic-preview').removeClass('hidden').addClass('flex');
            $('#modal-viewport-controls').removeClass('hidden').addClass('flex');
            $('#modal-dynamic-iframe').attr('src', dynamicUrl);
            setModalViewport('100%');
        } else {
            // Show classic static banner preview
            $('#modal-dynamic-preview').addClass('hidden').removeClass('flex');
            $('#modal-viewport-controls').addClass('hidden').removeClass('flex');
            $('#modal-static-preview').removeClass('hidden');
            $('#modal-banner-image').attr('src', image || '/images/banners/hero-grocery-1.jpg');
        }
        
        const $ctaBtn = $('#modal-banner-cta');
        const $linkTxt = $('#modal-banner-link-text');
        if (link) {
            $ctaBtn.attr('href', link);
            $linkTxt.text('Target Link: ' + link);
        } else {
            $ctaBtn.attr('href', '#');
            $linkTxt.text('Target Link: No link configured');
        }

        const $modal = $('#banner-preview-modal');
        $modal.removeClass('hidden').addClass('flex');

        if (window.Admin && typeof window.Admin.refreshIcons === 'function') {
            window.Admin.refreshIcons();
        }
    }

    function setModalViewport(width, btn) {
        $('#modal-iframe-wrapper').css('width', width);
        if (btn) {
            $('.modal-vp-btn').removeClass('active bg-white text-slate-900 shadow-2xs').addClass('text-slate-600');
            $(btn).addClass('active bg-white text-slate-900 shadow-2xs').removeClass('text-slate-600');
        }
    }

    function closeBannerPreview() {
        $('#modal-dynamic-iframe').attr('src', 'about:blank');
        $('#banner-preview-modal').addClass('hidden').removeClass('flex');
    }

    // AJAX Status Toggle with jQuery
    function toggleBannerStatus(id, checkbox) {
        const $checkbox = $(checkbox);
        $.ajax({
            url: `/admin/banners/${id}/toggle-status`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'success', title: 'Status Updated', message: data.message });
                    }
                } else {
                    $checkbox.prop('checked', !$checkbox.prop('checked'));
                    if (window.Admin && window.Admin.toast) {
                        Admin.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to update status.' });
                    }
                }
            },
            error: function() {
                $checkbox.prop('checked', !$checkbox.prop('checked'));
                if (window.Admin && window.Admin.toast) {
                    Admin.toast({ type: 'error', title: 'Server Error', message: 'Could not connect to server.' });
                }
            }
        });
    }

    // SweetAlert2 Delete Confirmation with jQuery
    function confirmBannerDelete(id, title) {
        if (window.Admin && window.Admin.confirm) {
            Admin.confirm({
                title: `Delete Banner?`,
                text: `Are you sure you want to delete "${title}"? This action cannot be undone.`,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc2626',
                onConfirm: () => {
                    $('#delete-banner-form').attr('action', `/admin/banners/${id}`).trigger('submit');
                }
            });
        }
    }
</script>
@endpush
