@extends('layouts.admin')

@section('content')
<div id="banner-builder-app" class="flex flex-col h-[calc(100vh-8.5rem)] min-h-[700px] -m-4 sm:-m-6 lg:-m-8 bg-slate-950 text-slate-100 overflow-hidden select-none font-sans">
    
    <!-- ========================================================================= -->
    <!-- 1. TOP STUDIO HEADER BAR -->
    <!-- ========================================================================= -->
    <header class="h-14 bg-slate-900/95 backdrop-blur-md border-b border-slate-800/80 px-4 flex items-center justify-between shrink-0 z-30 shadow-md">
        <!-- Left: Back, Banner Metadata & Status -->
        <div class="flex items-center gap-3.5 min-w-0">
            <a
                href="{{ route('admin.banners.index') }}"
                class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white transition-all flex items-center justify-center shrink-0 border border-slate-700/60"
                title="Return to Banners List"
            >
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>

            <div class="h-6 w-px bg-slate-800 hidden sm:block"></div>

            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white shadow-xs shrink-0">
                    <i data-lucide="palette" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            id="builder-banner-title"
                            value="{{ $banner->title }}"
                            class="bg-transparent hover:bg-slate-800/60 focus:bg-slate-800 text-sm font-bold text-white px-2 py-0.5 rounded-lg border border-transparent focus:border-emerald-500 focus:outline-hidden transition-all truncate max-w-[180px] sm:max-w-[240px]"
                            title="Click to rename banner headline"
                        />
                        <select
                            id="builder-banner-position"
                            class="hidden md:inline-block bg-slate-800 border border-slate-700 text-[11px] font-semibold text-emerald-400 rounded-lg px-2 py-0.5 focus:outline-hidden focus:border-emerald-500"
                            title="Select Banner Placement Zone"
                        >
                            <option value="home_hero" {{ $banner->position === 'home_hero' ? 'selected' : '' }}>Home Hero (1920×700)</option>
                            <option value="category_top" {{ $banner->position === 'category_top' ? 'selected' : '' }}>Category Top Banner</option>
                            <option value="popup" {{ $banner->position === 'popup' ? 'selected' : '' }}>Storefront Popup Modal</option>
                            <option value="promotional_bar" {{ $banner->position === 'promotional_bar' ? 'selected' : '' }}>Top Announcement Bar</option>
                            <option value="sidebar" {{ $banner->position === 'sidebar' ? 'selected' : '' }}>Sidebar Widget</option>
                        </select>
                        <button
                            type="button"
                            id="btn-open-schedule-modal"
                            class="hidden lg:inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-[11px] font-medium text-slate-300 transition-colors"
                            title="Configure Schedule, Dates & Status"
                        >
                            <i data-lucide="calendar" class="w-3 h-3 text-amber-400"></i>
                            <span>Schedule</span>
                            <span class="w-1.5 h-1.5 rounded-full {{ $banner->is_active ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Auto-save Sync Status Badge -->
            <div id="save-status-indicator" class="hidden xl:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-800/60 border border-slate-700/50 text-[11px] text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" id="save-status-dot"></span>
                <span id="save-status-text">All changes synced</span>
            </div>
        </div>

        <!-- Center: Device Switcher & History / Snapping Tools -->
        <div class="flex items-center gap-1.5 sm:gap-3">
            <!-- Viewport Switcher -->
            <div class="flex items-center bg-slate-800/90 rounded-xl p-0.5 border border-slate-700/70 shadow-xs">
                <button
                    type="button"
                    class="device-switch-btn px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all bg-emerald-600 text-white shadow-xs"
                    data-device="desktop"
                    data-width="1920"
                    data-height="700"
                    title="Desktop Wide Viewport (1920 × 700)"
                >
                    <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
                    <span class="hidden lg:inline">Desktop</span>
                </button>
                <button
                    type="button"
                    class="device-switch-btn px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 text-slate-400 hover:text-slate-200 transition-all"
                    data-device="tablet"
                    data-width="1024"
                    data-height="500"
                    title="Tablet Viewport (1024 × 500)"
                >
                    <i data-lucide="tablet" class="w-3.5 h-3.5"></i>
                    <span class="hidden lg:inline">Tablet</span>
                </button>
                <button
                    type="button"
                    class="device-switch-btn px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 text-slate-400 hover:text-slate-200 transition-all"
                    data-device="mobile"
                    data-width="480"
                    data-height="420"
                    title="Mobile Viewport (480 × 420)"
                >
                    <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                    <span class="hidden lg:inline">Mobile</span>
                </button>
            </div>

            <!-- History Controls -->
            <div class="hidden sm:flex items-center bg-slate-800/90 rounded-xl p-0.5 border border-slate-700/70">
                <button
                    type="button"
                    id="btn-undo"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-transparent transition-all cursor-pointer"
                    title="Undo (Ctrl+Z)"
                    disabled
                >
                    <i data-lucide="undo-2" class="w-4 h-4"></i>
                </button>
                <button
                    type="button"
                    id="btn-redo"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-transparent transition-all cursor-pointer"
                    title="Redo (Ctrl+Y)"
                    disabled
                >
                    <i data-lucide="redo-2" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Zoom & Grid Tools -->
            <div class="hidden md:flex items-center bg-slate-800/90 rounded-xl p-0.5 border border-slate-700/70">
                <button
                    type="button"
                    id="btn-zoom-out"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-all"
                    title="Zoom Out (-)"
                >
                    <i data-lucide="zoom-out" class="w-4 h-4"></i>
                </button>
                <button
                    type="button"
                    id="btn-zoom-reset"
                    class="px-2 py-1 text-xs font-mono font-medium text-slate-300 hover:text-white transition-colors"
                    title="Reset Zoom to Fit"
                >
                    <span id="zoom-level-text">Fit</span>
                </button>
                <button
                    type="button"
                    id="btn-zoom-in"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-all"
                    title="Zoom In (+)"
                >
                    <i data-lucide="zoom-in" class="w-4 h-4"></i>
                </button>
                <div class="h-4 w-px bg-slate-700 mx-0.5"></div>
                <button
                    type="button"
                    id="btn-toggle-grid"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-all"
                    title="Toggle Alignment Grid (G)"
                >
                    <i data-lucide="grid-3x3" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Right: Preview, Export & Save Actions -->
        <div class="flex items-center gap-2">
            <button
                type="button"
                id="btn-preview-mode"
                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold border border-slate-700/70 transition-all flex items-center gap-1.5 cursor-pointer"
                title="Preview Storefront Mockup"
            >
                <i data-lucide="eye" class="w-3.5 h-3.5 text-sky-400"></i>
                <span class="hidden sm:inline">Live Preview</span>
            </button>

            <!-- Export & Import Dropdown -->
            <div class="relative" id="export-dropdown-container">
                <button
                    type="button"
                    id="btn-export-dropdown"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold border border-slate-700/70 transition-all flex items-center gap-1.5 cursor-pointer"
                    title="Export Graphic or JSON Template"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span class="hidden sm:inline">Export</span>
                    <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                </button>

                <div
                    id="export-dropdown-menu"
                    class="absolute right-0 top-full mt-1.5 w-56 rounded-xl bg-slate-900 border border-slate-700 shadow-2xl p-1.5 z-50 space-y-1 hidden"
                >
                    <button type="button" id="btn-export-png" class="w-full px-2.5 py-1.5 rounded-lg text-left text-xs text-slate-200 hover:bg-slate-800 hover:text-white flex items-center gap-2 transition-colors">
                        <i data-lucide="image" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <div>
                            <span class="font-bold block">Download PNG (1920×700)</span>
                            <span class="text-[9px] text-slate-400">High-Res lossless raster</span>
                        </div>
                    </button>
                    <button type="button" id="btn-export-jpg" class="w-full px-2.5 py-1.5 rounded-lg text-left text-xs text-slate-200 hover:bg-slate-800 hover:text-white flex items-center gap-2 transition-colors">
                        <i data-lucide="file-image" class="w-3.5 h-3.5 text-sky-400"></i>
                        <div>
                            <span class="font-bold block">Download JPEG (1920×700)</span>
                            <span class="text-[9px] text-slate-400">Compressed web banner</span>
                        </div>
                    </button>
                    <div class="h-px bg-slate-800 my-1"></div>
                    <button type="button" id="btn-export-json" class="w-full px-2.5 py-1.5 rounded-lg text-left text-xs text-slate-200 hover:bg-slate-800 hover:text-white flex items-center gap-2 transition-colors">
                        <i data-lucide="code" class="w-3.5 h-3.5 text-amber-400"></i>
                        <div>
                            <span class="font-bold block">Export JSON Template</span>
                            <span class="text-[9px] text-slate-400">Design schema backup (.json)</span>
                        </div>
                    </button>
                    <button type="button" id="btn-import-json" class="w-full px-2.5 py-1.5 rounded-lg text-left text-xs text-slate-200 hover:bg-slate-800 hover:text-white flex items-center gap-2 transition-colors">
                        <i data-lucide="upload" class="w-3.5 h-3.5 text-teal-400"></i>
                        <div>
                            <span class="font-bold block">Import JSON Template</span>
                            <span class="text-[9px] text-slate-400">Restore from backup file</span>
                        </div>
                    </button>
                </div>
                <input type="file" id="import-json-file" accept=".json" class="hidden" />
            </div>

            <button
                type="button"
                id="btn-save-banner"
                class="px-4 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold shadow-md shadow-emerald-950/50 transition-all flex items-center gap-2 cursor-pointer group"
                title="Save Visual Banner Design (Ctrl+S)"
            >
                <i data-lucide="save" class="w-3.5 h-3.5 group-hover:scale-110 transition-transform"></i>
                <span>Save Banner</span>
            </button>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 2. MAIN 3-COLUMN STUDIO WORKSPACE -->
    <!-- ========================================================================= -->
    <div class="flex-1 flex min-h-0 relative overflow-hidden">

        <!-- --------------------------------------------------------------------- -->
        <!-- 2A. LEFT VERTICAL TOOL STRIP -->
        <!-- --------------------------------------------------------------------- -->
        <aside class="w-16 bg-slate-900/90 border-r border-slate-800/80 flex flex-col items-center py-3 gap-1.5 shrink-0 z-20">
            <button
                type="button"
                class="builder-tool-tab w-12 h-12 rounded-xl flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-white hover:bg-slate-800/70 transition-all active-tab bg-slate-800 text-emerald-400 border border-emerald-500/30"
                data-panel="templates"
                title="Templates & Presets"
            >
                <i data-lucide="layout-template" class="w-4 h-4"></i>
                <span class="text-[9px] font-semibold">Presets</span>
            </button>

            <button
                type="button"
                class="builder-tool-tab w-12 h-12 rounded-xl flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-white hover:bg-slate-800/70 transition-all"
                data-panel="elements"
                title="Badges & Promotional Stickers"
            >
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                <span class="text-[9px] font-semibold">Badges</span>
            </button>

            <button
                type="button"
                class="builder-tool-tab w-12 h-12 rounded-xl flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-white hover:bg-slate-800/70 transition-all"
                data-panel="text"
                title="Headings & Typography"
            >
                <i data-lucide="type" class="w-4 h-4"></i>
                <span class="text-[9px] font-semibold">Text</span>
            </button>

            <button
                type="button"
                class="builder-tool-tab w-12 h-12 rounded-xl flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-white hover:bg-slate-800/70 transition-all"
                data-panel="media"
                title="Backgrounds & Graphics"
            >
                <i data-lucide="image" class="w-4 h-4"></i>
                <span class="text-[9px] font-semibold">Media</span>
            </button>

            <button
                type="button"
                class="builder-tool-tab w-12 h-12 rounded-xl flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-white hover:bg-slate-800/70 transition-all"
                data-panel="products"
                title="Store Products & Live Catalog"
            >
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span class="text-[9px] font-semibold">Products</span>
            </button>

            <button
                type="button"
                class="builder-tool-tab w-12 h-12 rounded-xl flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-white hover:bg-slate-800/70 transition-all"
                data-panel="shapes"
                title="Shapes, Cards & Buttons"
            >
                <i data-lucide="shapes" class="w-4 h-4"></i>
                <span class="text-[9px] font-semibold">Shapes</span>
            </button>

            <div class="my-auto"></div>

            <button
                type="button"
                class="builder-tool-tab w-12 h-12 rounded-xl flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-white hover:bg-slate-800/70 transition-all"
                data-panel="layers"
                title="Element Layers & Ordering"
            >
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span class="text-[9px] font-semibold">Layers</span>
            </button>
        </aside>

        <!-- --------------------------------------------------------------------- -->
        <!-- 2B. EXPANDABLE TOOL DRAWER PANEL -->
        <!-- --------------------------------------------------------------------- -->
        <aside id="builder-drawer-panel" class="w-80 bg-slate-900/95 border-r border-slate-800/80 flex flex-col shrink-0 z-10 transition-all duration-200 overflow-hidden">
            
            <!-- Drawer Header & Search -->
            <div class="p-3.5 border-b border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <span id="drawer-panel-title" class="text-xs font-bold uppercase tracking-wider text-slate-200">Templates & Presets</span>
                </div>
                <button
                    type="button"
                    id="btn-collapse-drawer"
                    class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                    title="Collapse Panel"
                >
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Drawer Dynamic Tab Content Scroll Area -->
            <div class="flex-1 overflow-y-auto p-3.5 space-y-4 text-xs">
                
                <!-- TAB 1: PRESETS / TEMPLATES -->
                <div id="drawer-tab-templates" class="drawer-tab-content space-y-3.5">
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Choose a professionally crafted supermarket theme. Applying a template sets harmonious colors, badges, and layout hierarchy.
                    </p>
                    
                    <div class="space-y-2.5">
                        <!-- Template 1: Organic Fresh -->
                        <div class="template-card p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 hover:border-emerald-500/50 transition-all cursor-pointer group" data-template="organic_fresh">
                            <div class="h-20 rounded-lg bg-gradient-to-r from-emerald-950 via-teal-900 to-slate-900 relative overflow-hidden flex items-center px-3 border border-emerald-500/20">
                                <div>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500 text-white uppercase tracking-wider">100% Organic</span>
                                    <h4 class="text-xs font-bold text-white mt-1">Farm Harvest Daily</h4>
                                    <span class="text-[10px] text-emerald-300">2-Hour Fresh Delivery</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-700/40">
                                <span class="text-[11px] font-semibold text-slate-300">Organic Fresh Harvest</span>
                                <span class="text-[10px] text-emerald-400 font-medium group-hover:underline">Apply Preset</span>
                            </div>
                        </div>

                        <!-- Template 2: Dairy & Breakfast -->
                        <div class="template-card p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 hover:border-sky-500/50 transition-all cursor-pointer group" data-template="dairy_delight">
                            <div class="h-20 rounded-lg bg-gradient-to-r from-sky-950 via-indigo-900 to-slate-900 relative overflow-hidden flex items-center px-3 border border-sky-500/20">
                                <div>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-500 text-white uppercase tracking-wider">Morning Delivery</span>
                                    <h4 class="text-xs font-bold text-white mt-1">Pure Farm Dairy & Bakery</h4>
                                    <span class="text-[10px] text-sky-300">Delivered by 7:00 AM</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-700/40">
                                <span class="text-[11px] font-semibold text-slate-300">Morning Dairy & Bakery</span>
                                <span class="text-[10px] text-sky-400 font-medium group-hover:underline">Apply Preset</span>
                            </div>
                        </div>

                        <!-- Template 3: Flash Weekend Deals -->
                        <div class="template-card p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 transition-all cursor-pointer group" data-template="flash_deals">
                            <div class="h-20 rounded-lg bg-gradient-to-r from-rose-950 via-amber-950 to-slate-900 relative overflow-hidden flex items-center px-3 border border-amber-500/20">
                                <div>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-600 text-white uppercase tracking-wider">Flat 50% Off</span>
                                    <h4 class="text-xs font-bold text-amber-300 mt-1">Supermarket Flash Sale</h4>
                                    <span class="text-[10px] text-amber-200">Weekend Only Savings</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-700/40">
                                <span class="text-[11px] font-semibold text-slate-300">Weekend Supermarket Flash</span>
                                <span class="text-[10px] text-amber-400 font-medium group-hover:underline">Apply Preset</span>
                            </div>
                        </div>

                        <!-- Template 4: Exotic Fruits Fiesta -->
                        <div class="template-card p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 hover:border-purple-500/50 transition-all cursor-pointer group" data-template="exotic_fruits">
                            <div class="h-20 rounded-lg bg-gradient-to-r from-purple-950 via-fuchsia-950 to-slate-900 relative overflow-hidden flex items-center px-3 border border-purple-500/20">
                                <div>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-fuchsia-600 text-white uppercase tracking-wider">BOGO FREE</span>
                                    <h4 class="text-xs font-bold text-white mt-1">Exotic Summer Fruit Fest</h4>
                                    <span class="text-[10px] text-fuchsia-300">Berries & Dragonfruit</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-700/40">
                                <span class="text-[11px] font-semibold text-slate-300">Exotic Summer Fruit Fest</span>
                                <span class="text-[10px] text-purple-400 font-medium group-hover:underline">Apply Preset</span>
                            </div>
                        </div>

                        <!-- Template 5: Prime Cut Meat & Seafood -->
                        <div class="template-card p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 hover:border-rose-500/50 transition-all cursor-pointer group" data-template="meat_saver">
                            <div class="h-20 rounded-lg bg-gradient-to-r from-stone-900 via-rose-950 to-slate-950 relative overflow-hidden flex items-center px-3 border border-rose-500/20">
                                <div>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-600 text-white uppercase tracking-wider">100% Fresh Cuts</span>
                                    <h4 class="text-xs font-bold text-white mt-1">Prime Meat & Fresh Seafood</h4>
                                    <span class="text-[10px] text-rose-300">Antibiotic-Free Guarantee</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-700/40">
                                <span class="text-[11px] font-semibold text-slate-300">Prime Cut Meat & Seafood</span>
                                <span class="text-[10px] text-rose-400 font-medium group-hover:underline">Apply Preset</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: PROMO BADGES & STICKERS -->
                <div id="drawer-tab-elements" class="drawer-tab-content space-y-3.5 hidden">
                    <p class="text-[11px] text-slate-400">Click any promotional badge or sticker to insert it onto your canvas.</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" class="btn-insert-badge p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 text-center transition-all group" data-badge-text="🔥 FLAT 50% OFF" data-bg="#ef4444" data-color="#ffffff">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-rose-600 text-white font-extrabold text-[10px] shadow-xs">🔥 50% OFF</span>
                            <span class="block text-[10px] text-slate-400 mt-1">Flash Discount</span>
                        </button>

                        <button type="button" class="btn-insert-badge p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 text-center transition-all group" data-badge-text="🌿 100% CERTIFIED ORGANIC" data-bg="#16a34a" data-color="#ffffff">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-600 text-white font-bold text-[10px] shadow-xs">🌿 ORGANIC</span>
                            <span class="block text-[10px] text-slate-400 mt-1">Farm Organic</span>
                        </button>

                        <button type="button" class="btn-insert-badge p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 text-center transition-all group" data-badge-text="🎁 BUY 1 GET 1 FREE (BOGO)" data-bg="#f59e0b" data-color="#0f172a">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 font-black text-[10px] shadow-xs">🎁 BOGO FREE</span>
                            <span class="block text-[10px] text-slate-400 mt-1">Special Deal</span>
                        </button>

                        <button type="button" class="btn-insert-badge p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 text-center transition-all group" data-badge-text="⚡ EXPRESS 2HR DELIVERY" data-bg="#0284c7" data-color="#ffffff">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-sky-600 text-white font-bold text-[10px] shadow-xs">⚡ 2HR SPEED</span>
                            <span class="block text-[10px] text-slate-400 mt-1">Express Delivery</span>
                        </button>

                        <button type="button" class="btn-insert-badge p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 text-center transition-all group" data-badge-text="🚚 FREE SHIPPING OVER ₹499" data-bg="#059669" data-color="#ffffff">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-700 text-white font-bold text-[10px] shadow-xs">🚚 FREE SHIP</span>
                            <span class="block text-[10px] text-slate-400 mt-1">Free Delivery Tag</span>
                        </button>

                        <button type="button" class="btn-insert-badge p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 text-center transition-all group" data-badge-text="🛡️ BEST PRICE GUARANTEE" data-bg="#d97706" data-color="#ffffff">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-amber-600 text-white font-extrabold text-[10px] shadow-xs">🛡️ BEST PRICE</span>
                            <span class="block text-[10px] text-slate-400 mt-1">Guaranteed Price</span>
                        </button>

                        <button type="button" class="btn-insert-badge p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 text-center transition-all group" data-badge-text="🌱 100% VEGAN & PURE" data-bg="#0d9488" data-color="#ffffff">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-teal-600 text-white font-bold text-[10px] shadow-xs">🌱 VEGAN PURE</span>
                            <span class="block text-[10px] text-slate-400 mt-1">Plant Based Tag</span>
                        </button>

                        <button type="button" class="btn-insert-badge p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 text-center transition-all group" data-badge-text="☀️ FRESH MORNING HARVEST" data-bg="#e11d48" data-color="#ffffff">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-rose-600 text-white font-bold text-[10px] shadow-xs">☀️ FRESH DAILY</span>
                            <span class="block text-[10px] text-slate-400 mt-1">Morning Harvest</span>
                        </button>
                    </div>
                </div>

                <!-- TAB 3: TEXT & TYPOGRAPHY -->
                <div id="drawer-tab-text" class="drawer-tab-content space-y-3 hidden">
                    <p class="text-[11px] text-slate-400">Add custom typography and promotional headlines.</p>
                    <button type="button" id="btn-add-headline" class="w-full py-3 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-left transition-all">
                        <span class="text-base font-extrabold text-white block">Add Big Banner Headline</span>
                        <span class="text-[10px] text-slate-400">Large impactful display font (52px)</span>
                    </button>
                    <button type="button" id="btn-add-subtitle" class="w-full py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-left transition-all">
                        <span class="text-xs font-semibold text-slate-200 block">Add Supporting Subtitle</span>
                        <span class="text-[10px] text-slate-400">Secondary descriptive line (20px)</span>
                    </button>
                    <button type="button" id="btn-add-cta-btn" class="w-full py-2 px-3 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/40 text-left transition-all flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-emerald-400 block">Add Interactive Call to Action Button</span>
                            <span class="text-[10px] text-slate-400">Shop Now / Order Today CTA</span>
                        </div>
                        <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                    </button>
                </div>

                <!-- TAB 4: MEDIA & BACKGROUND GRAPHICS -->
                <div id="drawer-tab-media" class="drawer-tab-content space-y-3.5 hidden">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1.5">Upload Background Graphic / Sticker</label>
                        <div
                            id="builder-upload-zone"
                            class="border border-dashed border-slate-700 rounded-xl p-4 text-center hover:border-emerald-500 hover:bg-emerald-500/5 transition-all cursor-pointer group"
                        >
                            <input type="file" id="builder-file-uploader" accept="image/*" class="hidden" />
                            <i data-lucide="upload-cloud" class="w-6 h-6 mx-auto text-slate-400 group-hover:text-emerald-400 mb-1.5 transition-colors"></i>
                            <span class="text-[11px] font-semibold text-slate-300 block">Click or Drop High-Res Graphic</span>
                            <span class="text-[9px] text-slate-500 mt-0.5 block">JPG, PNG, WebP or SVG up to 4MB</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1.5">Curated Supermarket Stock Graphics</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="stock-media-item rounded-lg overflow-hidden border border-slate-700 cursor-pointer hover:border-emerald-500 transition-all" data-img="/images/banners/hero-grocery-1.jpg">
                                <img src="/images/banners/hero-grocery-1.jpg" alt="Produce" class="w-full h-16 object-cover" onerror="this.onerror=null;this.src='/images/placeholder.svg';" />
                                <span class="block text-[10px] text-slate-300 p-1 text-center bg-slate-800">Fresh Produce</span>
                            </div>
                            <div class="stock-media-item rounded-lg overflow-hidden border border-slate-700 cursor-pointer hover:border-emerald-500 transition-all" data-img="/images/banners/hero-grocery-2.jpg">
                                <img src="/images/banners/hero-grocery-2.jpg" alt="Dairy" class="w-full h-16 object-cover" onerror="this.onerror=null;this.src='/images/placeholder.svg';" />
                                <span class="block text-[10px] text-slate-300 p-1 text-center bg-slate-800">Dairy & Milk</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: PRODUCTS (CATALOG INTEGRATION) -->
                <div id="drawer-tab-products" class="drawer-tab-content space-y-3 hidden">
                    <p class="text-[11px] text-slate-400">Insert real products directly onto the banner with live prices and images.</p>
                    <input
                        type="text"
                        id="product-search-input"
                        placeholder="Search catalog products..."
                        class="w-full px-3 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white placeholder:text-slate-500 focus:outline-hidden focus:border-emerald-500"
                    />

                    <div id="product-picker-list" class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
                        @forelse($products as $prod)
                            <div
                                class="product-insert-card p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700/60 flex items-center justify-between cursor-pointer transition-all group"
                                data-product-id="{{ $prod->id }}"
                                data-product-name="{{ $prod->name }}"
                                data-product-price="{{ $prod->selling_price }}"
                                data-product-special="{{ $prod->special_price }}"
                                data-product-image="{{ $prod->thumbnail ?? '/images/placeholder.svg' }}"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img src="{{ $prod->thumbnail ?? '/images/placeholder.svg' }}" alt="{{ $prod->name }}" class="w-9 h-9 rounded-lg object-cover bg-slate-900 shrink-0" onerror="this.onerror=null;this.src='/images/placeholder.svg';" />
                                    <div class="min-w-0">
                                        <h5 class="text-[11px] font-bold text-slate-200 truncate group-hover:text-emerald-400 transition-colors">{{ $prod->name }}</h5>
                                        <span class="text-[10px] font-mono text-emerald-400 font-bold">₹{{ number_format($prod->selling_price, 2) }}</span>
                                    </div>
                                </div>
                                <button type="button" class="p-1 rounded-lg bg-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white transition-all shrink-0">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        @empty
                            <p class="text-[11px] text-slate-500 text-center py-4">No active products found in store catalog.</p>
                        @endforelse
                    </div>
                </div>

                <!-- TAB 6: SHAPES & CONTAINERS -->
                <div id="drawer-tab-shapes" class="drawer-tab-content space-y-3 hidden">
                    <p class="text-[11px] text-slate-400">Insert geometric overlays, backdrop cards, and accent ribbons.</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" class="btn-insert-shape p-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-center transition-all" data-shape="card" data-bg="rgba(15, 23, 42, 0.75)">
                            <div class="w-10 h-6 mx-auto rounded-lg bg-slate-700 border border-slate-600 mb-1.5"></div>
                            <span class="text-[10px] font-semibold text-slate-300">Glass Backdrop Card</span>
                        </button>
                        <button type="button" class="btn-insert-shape p-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-center transition-all" data-shape="pill" data-bg="#16a34a">
                            <div class="w-10 h-5 mx-auto rounded-full bg-emerald-600 mb-1.5"></div>
                            <span class="text-[10px] font-semibold text-slate-300">Pill Ribbon</span>
                        </button>
                        <button type="button" class="btn-insert-shape p-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-center transition-all" data-shape="circle" data-bg="#ea580c">
                            <div class="w-6 h-6 mx-auto rounded-full bg-amber-600 mb-1.5"></div>
                            <span class="text-[10px] font-semibold text-slate-300">Discount Circle</span>
                        </button>
                        <button type="button" class="btn-insert-shape p-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-center transition-all" data-shape="divider" data-bg="#10b981">
                            <div class="w-10 h-1 mx-auto bg-emerald-500 my-4"></div>
                            <span class="text-[10px] font-semibold text-slate-300">Accent Line</span>
                        </button>
                    </div>
                </div>

                <!-- TAB 7: LAYER TREE -->
                <div id="drawer-tab-layers" class="drawer-tab-content space-y-2.5 hidden">
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>Layer Hierarchy (Top to Bottom)</span>
                    </div>
                    <div id="layers-tree-list" class="space-y-1.5">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

            </div>
        </aside>

        <!-- --------------------------------------------------------------------- -->
        <!-- 2C. CENTER CANVAS STAGE -->
        <!-- --------------------------------------------------------------------- -->
        <main id="builder-stage-wrapper" class="flex-1 bg-[#090d16] relative flex items-center justify-center overflow-hidden p-6 select-none">
            
            <!-- Canvas Background Grid Guide (Toggleable) -->
            <div id="canvas-grid-overlay" class="absolute inset-0 pointer-events-none opacity-20 bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px]"></div>

            <!-- Scalable Virtual Canvas Outer Frame -->
            <div id="canvas-viewport-frame" class="relative shrink-0 transition-transform duration-100 ease-out origin-center shadow-2xl rounded-2xl overflow-hidden ring-1 ring-slate-800" style="width: 1920px; height: 700px; min-width: 1920px; min-height: 700px;">
                
                <!-- The Canonical 1920x700 Canvas Root -->
                <div
                    id="banner-canvas"
                    style="width: 1920px; height: 700px; min-width: 1920px; min-height: 700px; background-color: {{ $designConfig['canvas']['backgroundColor'] ?? '#f8fafc' }};"
                    class="relative overflow-hidden cursor-default transition-colors duration-150 shrink-0"
                >
                    <!-- Canvas Background Image Layer -->
                    <div
                        id="canvas-bg-layer"
                        class="absolute inset-0 bg-cover bg-center pointer-events-none transition-all"
                        style="background-image: url('{{ $designConfig['canvas']['backgroundImage'] ?? $banner->image }}'); background-size: cover; background-position: center;"
                    ></div>

                    <!-- Canvas Color / Dimming Overlay Layer -->
                    <div
                        id="canvas-overlay-layer"
                        class="absolute inset-0 pointer-events-none transition-all"
                        style="background-color: {{ $designConfig['canvas']['overlayColor'] ?? '#000000' }}; opacity: {{ ($designConfig['canvas']['overlayOpacity'] ?? 0) / 100 }};"
                    ></div>

                    <!-- Elements Container Layer -->
                    <div id="canvas-elements-container" class="absolute inset-0 z-10">
                        <!-- Interactive Elements rendered dynamically via JS -->
                    </div>

                    <!-- Smart Snapping Guide Lines (Horizontal & Vertical) -->
                    <div id="snap-guide-x" class="absolute top-0 bottom-0 w-px bg-rose-500/80 z-50 pointer-events-none hidden"></div>
                    <div id="snap-guide-y" class="absolute left-0 right-0 h-px bg-rose-500/80 z-50 pointer-events-none hidden"></div>
                </div>

            </div>

            <!-- Floating Quick Action Element Toolbar -->
            <div id="canvas-floating-toolbar" class="absolute top-6 left-1/2 -translate-x-1/2 bg-slate-900/95 backdrop-blur-md border border-slate-700/80 rounded-xl px-3 py-1.5 shadow-2xl flex items-center gap-1 z-30 hidden text-xs transition-all duration-150 pointer-events-auto">
                <button type="button" id="float-btn-bring-front" class="p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-colors" title="Bring to Front">
                    <i data-lucide="arrow-up-to-line" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" id="float-btn-send-back" class="p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-colors" title="Send to Back">
                    <i data-lucide="arrow-down-to-line" class="w-3.5 h-3.5"></i>
                </button>
                <div class="h-4 w-px bg-slate-700 mx-1"></div>
                <button type="button" id="float-btn-flip-h" class="p-1.5 rounded-lg text-slate-300 hover:text-sky-400 hover:bg-slate-800 transition-colors" title="Flip Horizontal">
                    <i data-lucide="flip-horizontal" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" id="float-btn-flip-v" class="p-1.5 rounded-lg text-slate-300 hover:text-sky-400 hover:bg-slate-800 transition-colors" title="Flip Vertical">
                    <i data-lucide="flip-vertical" class="w-3.5 h-3.5"></i>
                </button>
                <div class="h-4 w-px bg-slate-700 mx-1"></div>
                <button type="button" id="float-btn-duplicate" class="p-1.5 rounded-lg text-slate-300 hover:text-emerald-400 hover:bg-slate-800 transition-colors" title="Duplicate Element (Ctrl+D)">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" id="float-btn-lock" class="p-1.5 rounded-lg text-slate-300 hover:text-amber-400 hover:bg-slate-800 transition-colors" title="Lock / Unlock Position">
                    <i data-lucide="unlock" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" id="float-btn-delete" class="p-1.5 rounded-lg text-slate-300 hover:text-rose-400 hover:bg-slate-800 transition-colors" title="Delete Element (Del)">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </main>

        <!-- --------------------------------------------------------------------- -->
        <!-- 2D. RIGHT PROPERTIES INSPECTOR PANEL -->
        <!-- --------------------------------------------------------------------- -->
        <aside id="builder-inspector-panel" class="w-80 bg-slate-900/95 border-l border-slate-800/80 flex flex-col shrink-0 z-20">
            
            <!-- Inspector Header -->
            <div class="p-3.5 border-b border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i data-lucide="sliders" class="w-4 h-4 text-emerald-400"></i>
                    <span id="inspector-title" class="text-xs font-bold uppercase tracking-wider text-slate-200">Canvas Properties</span>
                </div>
            </div>

            <!-- Inspector Form Controls Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 text-xs">
                
                <!-- SECTION: CANVAS SETTINGS (Shown when no element selected) -->
                <div id="inspector-canvas-props" class="space-y-4">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1.5">Canvas Base Background Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" id="prop-canvas-bgcolor" value="{{ $designConfig['canvas']['backgroundColor'] ?? '#f8fafc' }}" class="w-8 h-8 rounded-lg border-0 bg-transparent cursor-pointer" />
                            <input type="text" id="prop-canvas-bgcolor-hex" value="{{ $designConfig['canvas']['backgroundColor'] ?? '#f8fafc' }}" class="flex-1 px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs font-mono text-white" />
                        </div>
                        <!-- Quick Supermarket Palettes Swatches -->
                        <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                            <button type="button" class="canvas-color-swatch w-5 h-5 rounded-md border border-slate-700 hover:scale-110 transition-transform" style="background-color: #064e3b;" data-color="#064e3b" title="Organic Forest Green"></button>
                            <button type="button" class="canvas-color-swatch w-5 h-5 rounded-md border border-slate-700 hover:scale-110 transition-transform" style="background-color: #0f172a;" data-color="#0f172a" title="Midnight Navy"></button>
                            <button type="button" class="canvas-color-swatch w-5 h-5 rounded-md border border-slate-700 hover:scale-110 transition-transform" style="background-color: #78350f;" data-color="#78350f" title="Bakery Amber"></button>
                            <button type="button" class="canvas-color-swatch w-5 h-5 rounded-md border border-slate-700 hover:scale-110 transition-transform" style="background-color: #881337;" data-color="#881337" title="Crimson Flash"></button>
                            <button type="button" class="canvas-color-swatch w-5 h-5 rounded-md border border-slate-700 hover:scale-110 transition-transform" style="background-color: #0c4a6e;" data-color="#0c4a6e" title="Deep Ocean Blue"></button>
                            <button type="button" class="canvas-color-swatch w-5 h-5 rounded-md border border-slate-700 hover:scale-110 transition-transform" style="background-color: #f8fafc;" data-color="#f8fafc" title="Clean White/Slate"></button>
                        </div>
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1.5">Overlay Dimmer Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" id="prop-canvas-overlaycolor" value="{{ $designConfig['canvas']['overlayColor'] ?? '#000000' }}" class="w-8 h-8 rounded-lg border-0 bg-transparent cursor-pointer" />
                            <input type="text" id="prop-canvas-overlaycolor-hex" value="{{ $designConfig['canvas']['overlayColor'] ?? '#000000' }}" class="flex-1 px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs font-mono text-white" />
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[11px] font-semibold text-slate-300">Overlay Opacity</label>
                            <span id="prop-canvas-opacity-val" class="font-mono text-slate-400">{{ $designConfig['canvas']['overlayOpacity'] ?? 0 }}%</span>
                        </div>
                        <input type="range" id="prop-canvas-opacity" min="0" max="95" value="{{ $designConfig['canvas']['overlayOpacity'] ?? 0 }}" class="w-full accent-emerald-500 cursor-pointer" />
                    </div>

                    <div class="pt-2 border-t border-slate-800">
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1.5">Canvas Virtual Dimensions</label>
                        <div class="grid grid-cols-2 gap-2 text-slate-400 font-mono">
                            <div class="p-2 rounded-lg bg-slate-800/80 border border-slate-700 text-center">
                                <span class="text-[10px] block text-slate-500">Width</span>
                                <span class="text-xs font-bold text-slate-200">1920 px</span>
                            </div>
                            <div class="p-2 rounded-lg bg-slate-800/80 border border-slate-700 text-center">
                                <span class="text-[10px] block text-slate-500">Height</span>
                                <span class="text-xs font-bold text-slate-200">700 px</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION: ELEMENT SPECIFIC PROPERTIES (Shown when element selected) -->
                <div id="inspector-element-props" class="space-y-4 hidden">
                    
                    <!-- Content (For Text, Button, Badge) -->
                    <div id="field-content-group">
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1.5">Text Content / Headline</label>
                        <textarea id="prop-element-content" rows="2" class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white placeholder:text-slate-500 focus:outline-hidden focus:border-emerald-500"></textarea>
                    </div>

                    <!-- Destination Link (For Button / Clickable Element) -->
                    <div id="field-url-group">
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1.5">Target CTA URL Destination</label>
                        <input type="text" id="prop-element-url" placeholder="/categories/fresh or https://..." class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white placeholder:text-slate-500 focus:outline-hidden focus:border-emerald-500" />
                    </div>

                    <!-- Product Embed Card Settings (For Product Elements) -->
                    <div id="field-product-group" class="space-y-3 pt-2 border-t border-slate-800 hidden">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Product Card Customization</label>
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Card Theme Style</label>
                            <select id="prop-product-theme" class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white">
                                <option value="dark-glass">Dark Frosted Glass (Default)</option>
                                <option value="light-pill">Light Crisp Card</option>
                                <option value="flash-deal">Flash Deal Amber/Gold</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Product Title</label>
                            <input type="text" id="prop-product-name" class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Display Price (₹)</label>
                                <input type="text" id="prop-product-price" class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white font-mono" />
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Corner Tag Badge</label>
                                <input type="text" id="prop-product-badge" placeholder="FEATURED" class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white uppercase font-bold" />
                            </div>
                        </div>
                    </div>

                    <!-- Typography Styling -->
                    <div id="field-typography-group" class="space-y-3 pt-2 border-t border-slate-800">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Typography & Color</label>
                        
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Font Family</label>
                            <select id="prop-font-family" class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white">
                                <option value="Instrument Sans">Instrument Sans (Clean Modern)</option>
                                <option value="Inter">Inter (Digital Sans)</option>
                                <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                                <option value="Outfit">Outfit (Geometric Display)</option>
                                <option value="Playfair Display">Playfair Display (Premium Serif)</option>
                                <option value="Bebas Neue">Bebas Neue (Bold Impact)</option>
                                <option value="Montserrat">Montserrat</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Font Size (px)</label>
                                <input type="number" id="prop-font-size" min="10" max="160" class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white font-mono" />
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Font Weight</label>
                                <select id="prop-font-weight" class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white">
                                    <option value="400">Regular (400)</option>
                                    <option value="500">Medium (500)</option>
                                    <option value="600">SemiBold (600)</option>
                                    <option value="700">Bold (700)</option>
                                    <option value="800">ExtraBold (800)</option>
                                    <option value="900">Black (900)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Text Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="prop-text-color" class="w-8 h-8 rounded-lg border-0 bg-transparent cursor-pointer" />
                                <input type="text" id="prop-text-color-hex" class="flex-1 px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs font-mono text-white" />
                            </div>
                            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                <button type="button" class="text-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #ffffff;" data-color="#ffffff" title="Pure White"></button>
                                <button type="button" class="text-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #fde047;" data-color="#fde047" title="Vibrant Yellow"></button>
                                <button type="button" class="text-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #34d399;" data-color="#34d399" title="Mint Green"></button>
                                <button type="button" class="text-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #38bdf8;" data-color="#38bdf8" title="Sky Blue"></button>
                                <button type="button" class="text-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #0f172a;" data-color="#0f172a" title="Midnight Black"></button>
                            </div>
                        </div>

                        <!-- Line Height & Letter Spacing -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Line Height</label>
                                <input type="number" id="prop-line-height" step="0.05" min="0.8" max="2.5" class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white font-mono" />
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Letter Spacing (px)</label>
                                <input type="number" id="prop-letter-spacing" step="0.5" min="-5" max="20" class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white font-mono" />
                            </div>
                        </div>

                        <!-- Text Shadow -->
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Text Shadow / Glow Preset</label>
                            <select id="prop-text-shadow" class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white">
                                <option value="none">None</option>
                                <option value="soft">Soft Dark Shadow</option>
                                <option value="strong">Strong Drop Shadow</option>
                                <option value="outline">Dark Stroke Outline</option>
                                <option value="glow-emerald">Emerald Neon Glow</option>
                                <option value="glow-amber">Amber Neon Glow</option>
                                <option value="glow-sky">Sky Blue Glow</option>
                            </select>
                        </div>

                        <!-- Alignment -->
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Text Alignment</label>
                            <div class="flex items-center bg-slate-800 rounded-lg p-0.5 border border-slate-700">
                                <button type="button" class="btn-text-align flex-1 py-1 rounded text-center text-slate-400 hover:text-white" data-align="left">
                                    <i data-lucide="align-left" class="w-3.5 h-3.5 mx-auto"></i>
                                </button>
                                <button type="button" class="btn-text-align flex-1 py-1 rounded text-center text-slate-400 hover:text-white" data-align="center">
                                    <i data-lucide="align-center" class="w-3.5 h-3.5 mx-auto"></i>
                                </button>
                                <button type="button" class="btn-text-align flex-1 py-1 rounded text-center text-slate-400 hover:text-white" data-align="right">
                                    <i data-lucide="align-right" class="w-3.5 h-3.5 mx-auto"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Shape & Button Appearance -->
                    <div id="field-appearance-group" class="space-y-3 pt-2 border-t border-slate-800">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Appearance & Background</label>
                        
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Background Fill Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="prop-bg-color" class="w-8 h-8 rounded-lg border-0 bg-transparent cursor-pointer" />
                                <input type="text" id="prop-bg-color-hex" class="flex-1 px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs font-mono text-white" />
                            </div>
                            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                <button type="button" class="element-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #16a34a;" data-color="#16a34a" title="Emerald"></button>
                                <button type="button" class="element-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #ef4444;" data-color="#ef4444" title="Rose Red"></button>
                                <button type="button" class="element-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #f59e0b;" data-color="#f59e0b" title="Amber"></button>
                                <button type="button" class="element-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #0284c7;" data-color="#0284c7" title="Sky Blue"></button>
                                <button type="button" class="element-color-swatch w-4 h-4 rounded border border-slate-700 hover:scale-110 transition-transform" style="background-color: #0f172a;" data-color="#0f172a" title="Dark Slate"></button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Corner Radius (px)</label>
                                <input type="number" id="prop-border-radius" min="0" max="100" class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white font-mono" />
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Opacity (%)</label>
                                <input type="number" id="prop-opacity" min="0" max="100" class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white font-mono" />
                            </div>
                        </div>
                    </div>

                    <!-- Transform & Arrange Tools -->
                    <div class="pt-2 border-t border-slate-800 space-y-3">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Position & Geometry</label>
                        <div class="grid grid-cols-2 gap-2 font-mono text-[11px]">
                            <div>
                                <label class="text-[9px] text-slate-500 block">X (px / %)</label>
                                <input type="number" id="prop-pos-x" class="w-full px-2 py-1 bg-slate-800 border border-slate-700 rounded text-slate-200" />
                            </div>
                            <div>
                                <label class="text-[9px] text-slate-500 block">Y (px / %)</label>
                                <input type="number" id="prop-pos-y" class="w-full px-2 py-1 bg-slate-800 border border-slate-700 rounded text-slate-200" />
                            </div>
                            <div>
                                <label class="text-[9px] text-slate-500 block">Width (px)</label>
                                <input type="number" id="prop-pos-w" class="w-full px-2 py-1 bg-slate-800 border border-slate-700 rounded text-slate-200" />
                            </div>
                            <div>
                                <label class="text-[9px] text-slate-500 block">Height (px)</label>
                                <input type="number" id="prop-pos-h" class="w-full px-2 py-1 bg-slate-800 border border-slate-700 rounded text-slate-200" />
                            </div>
                        </div>

                        <!-- Alignment Quick Actions -->
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[10px] text-slate-400 block font-semibold">Snap & Align Layer</span>
                            <div class="grid grid-cols-3 gap-1.5">
                                <button type="button" id="btn-align-left" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-center text-[10px] font-bold transition-all" title="Align Left">Left</button>
                                <button type="button" id="btn-align-center-h" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-center text-[10px] font-bold transition-all" title="Center Horizontally">Center H</button>
                                <button type="button" id="btn-align-right" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-center text-[10px] font-bold transition-all" title="Align Right">Right</button>
                                <button type="button" id="btn-align-top" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-center text-[10px] font-bold transition-all" title="Align Top">Top</button>
                                <button type="button" id="btn-align-center-v" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-center text-[10px] font-bold transition-all" title="Center Vertically">Center V</button>
                                <button type="button" id="btn-align-bottom" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-center text-[10px] font-bold transition-all" title="Align Bottom">Bottom</button>
                            </div>
                            <button type="button" id="btn-align-center-both" class="w-full py-1 rounded-lg bg-slate-800/90 hover:bg-emerald-600/30 border border-slate-700 hover:border-emerald-500/40 text-slate-300 hover:text-emerald-300 text-[10px] font-bold transition-all" title="Center Layer in Both Axes">Center Canvas Both Axes</button>
                        </div>
                    </div>

                    <!-- Responsive Device Visibility -->
                    <div class="pt-2 border-t border-slate-800 space-y-2">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Device Visibility</label>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-slate-300 text-xs cursor-pointer">
                                <input type="checkbox" id="prop-hide-mobile" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-0" />
                                <span>Hide on Mobile Devices (&lt; 640px)</span>
                            </label>
                            <label class="flex items-center gap-2 text-slate-300 text-xs cursor-pointer">
                                <input type="checkbox" id="prop-hide-desktop" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-0" />
                                <span>Hide on Desktop / Tablet (&ge; 640px)</span>
                            </label>
                        </div>
                    </div>

                </div>

            </div>
        </aside>

    </div>

    <!-- ========================================================================= -->
    <!-- 3. BOTTOM STATUS & TELEMETRY BAR -->
    <!-- ========================================================================= -->
    <footer class="h-9 bg-slate-900 border-t border-slate-800 px-4 flex items-center justify-between text-[11px] font-mono text-slate-400 shrink-0 z-30">
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1.5">
                <i data-lucide="maximize-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                <span>Canvas: <strong class="text-slate-200" id="status-canvas-size">1920 × 700 px</strong></span>
            </span>
            <span class="hidden sm:inline text-slate-600">•</span>
            <span class="hidden sm:flex items-center gap-1.5">
                <span>Selected: <strong class="text-emerald-400 font-sans" id="status-selected-elem">None</strong></span>
            </span>
        </div>

        <div class="flex items-center gap-4">
            <span id="status-telemetry" class="hidden md:inline">X: 0px | Y: 0px | W: 0px | H: 0px</span>
            <span class="hidden md:inline text-slate-600">•</span>
            <span>Zoom: <strong class="text-slate-200" id="status-zoom-scale">100%</strong></span>
        </div>
    </footer>

    <!-- ========================================================================= -->
    <!-- 4. STOREFRONT LIVE CUSTOMER PREVIEW MODAL -->
    <!-- ========================================================================= -->
    <div id="modal-live-preview" class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-50 flex flex-col hidden">
        <!-- Modal Top Bar -->
        <div class="h-14 bg-slate-900 border-b border-slate-800 px-6 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Live Storefront Customer Simulation</h3>
                    <p class="text-[11px] text-slate-400">Exact rendering preview of how visitors see this banner on your website.</p>
                </div>
            </div>

            <!-- Device Switcher inside Preview -->
            <div class="flex items-center gap-1.5 bg-slate-950 p-1 rounded-xl border border-slate-800">
                <button type="button" class="preview-device-switch px-3 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 bg-emerald-600 text-white shadow-xs" data-preview-device="desktop">
                    <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
                    <span>Desktop (1920px)</span>
                </button>
                <button type="button" class="preview-device-switch px-3 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 text-slate-400 hover:text-slate-200" data-preview-device="mobile">
                    <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                    <span>Mobile Phone</span>
                </button>
            </div>

            <!-- Close Modal -->
            <button type="button" id="btn-close-preview-modal-x" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors" title="Close Preview (Esc)">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Body: Scrollable Storefront Simulation -->
        <div class="flex-1 overflow-y-auto p-6 flex justify-center bg-slate-950/60">
            <div id="preview-storefront-wrapper" class="w-full max-w-6xl transition-all duration-300">
                <div id="preview-phone-bezel" class="transition-all duration-300">
                    <!-- Simulated Storefront Shell -->
                    <div class="bg-white text-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200">
                        
                        <!-- Top Announcement Bar -->
                        <div class="bg-emerald-700 text-white text-[11px] font-medium py-1.5 px-4 flex items-center justify-between">
                            <span>🚚 Free Delivery on Grocery Orders above ₹499</span>
                            <span class="hidden sm:inline">📞 Express 24/7 Helpline: 1800-123-GROCERY</span>
                        </div>

                        <!-- Storefront Header Navigation -->
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-base shadow-sm">S</div>
                                <span class="text-lg font-black text-slate-900 tracking-tight">Shivoham<span class="text-emerald-600">Fresh</span></span>
                            </div>

                            <div class="flex-1 max-w-md hidden md:flex items-center bg-slate-100 rounded-xl px-3 py-1.5 border border-slate-200">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 mr-2"></i>
                                <span class="text-xs text-slate-400">Search 5,000+ organic groceries & farm fresh items...</span>
                            </div>

                            <div class="flex items-center gap-3 text-xs font-semibold text-slate-700">
                                <span class="hidden sm:inline">My Account</span>
                                <div class="flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-xl border border-emerald-200 font-bold">
                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                    <span>Cart (3 items)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Category Navigation Strip -->
                        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200/60 flex items-center gap-4 text-xs font-semibold text-slate-600 overflow-x-auto">
                            <span class="text-emerald-600 border-b-2 border-emerald-600 pb-0.5">All Categories</span>
                            <span>🍎 Fresh Fruits & Vegetables</span>
                            <span>🥛 Farm Dairy & Bakery</span>
                            <span>🌾 Rice, Atta & Dals</span>
                            <span>🍫 Snacks & Beverages</span>
                            <span>🥩 Fresh Meat & Seafood</span>
                        </div>

                        <!-- ------------------------------------------------------------- -->
                        <!-- LIVE BANNER RENDER CONTAINER -->
                        <!-- ------------------------------------------------------------- -->
                        <div class="p-4 sm:p-6">
                            <div id="preview-banner-render-box" class="w-full">
                                <!-- Dynamic Rendered Banner -->
                            </div>
                        </div>

                        <!-- Mock Deals Grid Below Banner -->
                        <div class="p-6 bg-slate-50 border-t border-slate-100">
                            <h4 class="text-sm font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Trending Storefront Flash Deals
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs">
                                    <span class="text-[10px] font-bold text-rose-600 uppercase">20% OFF</span>
                                    <h5 class="font-bold text-slate-800 mt-1 truncate">Farm Fresh Alphonso Mangoes</h5>
                                    <span class="text-emerald-700 font-extrabold block mt-1">₹399 / kg</span>
                                </div>
                                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs">
                                    <span class="text-[10px] font-bold text-emerald-600 uppercase">ORGANIC</span>
                                    <h5 class="font-bold text-slate-800 mt-1 truncate">Pure Desi Cow Ghee 1L</h5>
                                    <span class="text-emerald-700 font-extrabold block mt-1">₹649.00</span>
                                </div>
                                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs">
                                    <span class="text-[10px] font-bold text-amber-600 uppercase">BOGO</span>
                                    <h5 class="font-bold text-slate-800 mt-1 truncate">Whole Wheat Farm Atta 5kg</h5>
                                    <span class="text-emerald-700 font-extrabold block mt-1">₹235.00</span>
                                </div>
                                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs">
                                    <span class="text-[10px] font-bold text-sky-600 uppercase">FRESH</span>
                                    <h5 class="font-bold text-slate-800 mt-1 truncate">Cold Pressed Mustard Oil</h5>
                                    <span class="text-emerald-700 font-extrabold block mt-1">₹175.00</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. BANNER SCHEDULING, DATES & AUDIENCE MODAL -->
    <!-- ========================================================================= -->
    <div id="modal-banner-schedule" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="h-12 bg-slate-800/80 border-b border-slate-700/60 px-5 flex items-center justify-between">
                <div class="flex items-center gap-2 text-white text-xs font-bold">
                    <i data-lucide="calendar" class="w-4 h-4 text-amber-400"></i>
                    <span>Banner Schedule & Campaign Dates</span>
                </div>
                <button type="button" id="btn-close-schedule-modal" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="flex items-center justify-between p-3 rounded-xl bg-slate-800/60 border border-slate-700/60 cursor-pointer">
                        <div>
                            <span class="text-xs font-bold text-white block">Active Status</span>
                            <span class="text-[10px] text-slate-400">Publish this banner on customer storefront</span>
                        </div>
                        <input type="checkbox" id="builder-banner-active" class="w-4 h-4 rounded text-emerald-500 bg-slate-900 border-slate-700 focus:ring-0" {{ $banner->is_active ? 'checked' : '' }} />
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1">Start Date & Time</label>
                        <input type="datetime-local" id="builder-banner-starts-at" value="{{ $banner->starts_at ? $banner->starts_at->format('Y-m-d\TH:i') : '' }}" class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white" />
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1">Expiration Date & Time</label>
                        <input type="datetime-local" id="builder-banner-expires-at" value="{{ $banner->expires_at ? $banner->expires_at->format('Y-m-d\TH:i') : '' }}" class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white" />
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-semibold text-slate-300 block mb-1">Sort Priority Order (0 = Top / First)</label>
                    <input type="number" id="builder-banner-sort-order" value="{{ $banner->sort_order }}" min="0" max="999" class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white font-mono" />
                </div>
            </div>
            <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-end gap-2">
                <button type="button" id="btn-save-schedule-apply" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all cursor-pointer">
                    Apply Schedule Settings
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Pass Initial Design Configuration to JavaScript & Auto Initialize Studio -->
<script>
    window.__BANNER_BUILDER_DATA__ = {
        bannerId: {{ $banner->id }},
        saveUrl: "{{ route('admin.banners.builder.save', $banner) }}",
        uploadAssetUrl: "{{ route('admin.banners.builder.upload-asset', $banner) }}",
        csrfToken: "{{ csrf_token() }}",
        designConfig: @json($designConfig)
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (window.BannerBuilderFonts) {
            window.BannerBuilderFonts.init();
        }
        if (window.BannerBuilderShapes) {
            window.BannerBuilderShapes.init();
        }
        if (window.BannerBuilderPalettes) {
            window.BannerBuilderPalettes.init();
        }
        if (window.BannerBuilderState && window.__BANNER_BUILDER_DATA__) {
            window.BannerBuilderState.init(window.__BANNER_BUILDER_DATA__);
            if (window.BannerBuilderRenderer) {
                window.BannerBuilderRenderer.init();
            }
            if (window.BannerBuilderSelection) {
                window.BannerBuilderSelection.init();
            }
            if (window.BannerBuilderTransformer) {
                window.BannerBuilderTransformer.init();
            }
            if (window.BannerBuilderTemplates) {
                window.BannerBuilderTemplates.init();
            }
            if (window.BannerBuilderInserter) {
                window.BannerBuilderInserter.init();
            }
            if (window.BannerBuilderHistory) {
                window.BannerBuilderHistory.init();
            }
            if (window.BannerBuilderViewport) {
                window.BannerBuilderViewport.init();
            }
            if (window.BannerBuilderFloatingToolbar) {
                window.BannerBuilderFloatingToolbar.init();
            }
            if (window.BannerBuilderAligner) {
                window.BannerBuilderAligner.init();
            }
            if (window.BannerBuilderPreview) {
                window.BannerBuilderPreview.init();
            }
            if (window.BannerBuilderExporter) {
                window.BannerBuilderExporter.init();
            }
            if (window.BannerBuilderSaver) {
                window.BannerBuilderSaver.init();
            }
        }
    });
</script>
@endsection
