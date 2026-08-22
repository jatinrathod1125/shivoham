@props([
    'banner',
    'class' => '',
])

@php
    $design = $banner->effective_design_config ?? [];
    $canvas = $design['canvas'] ?? [];
    $elements = $design['elements'] ?? [];
    $hasVisualElements = !empty($elements) && is_array($elements);
@endphp

<div {{ $attributes->merge(['class' => 'storefront-visual-banner relative w-full overflow-hidden rounded-2xl shadow-lg ' . $class]) }}
     role="region"
     aria-label="Promotional Banner: {{ $banner->title }}"
     style="aspect-ratio: 1920 / 700; background-color: {{ $canvas['backgroundColor'] ?? '#f8fafc' }};">

    @if (!empty($canvas['backgroundImage']))
        <!-- Background Graphic Layer -->
        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 hover:scale-105"
             style="background-image: url('{{ $canvas['backgroundImage'] }}');"></div>
    @elseif(!empty($banner->image))
        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 hover:scale-105"
             style="background-image: url('{{ $banner->image }}');"></div>
    @endif

    @if (($canvas['overlayOpacity'] ?? 0) > 0)
        <!-- Overlay Dimmer Layer -->
        <div class="absolute inset-0 pointer-events-none"
             style="background-color: {{ $canvas['overlayColor'] ?? '#000000' }}; opacity: {{ ($canvas['overlayOpacity'] ?? 0) / 100 }};"></div>
    @endif

    @if ($hasVisualElements)
        <!-- Interactive Elements Layer -->
        <div class="absolute inset-0 z-10 pointer-events-auto">
            @foreach ($elements as $elem)
                @continue(empty($elem['visible']))

                @php
                    $style = $elem['style'] ?? [];
                    $scaleX = !empty($style['flipH']) ? -1 : 1;
                    $scaleY = !empty($style['flipV']) ? -1 : 1;
                    $rot = $elem['rotation'] ?? 0;
                    $transform = "rotate({$rot}deg) scale({$scaleX}, {$scaleY})";
                    $zIndex = $elem['zIndex'] ?? 10;
                    $type = $elem['type'] ?? 'text';
                    $hasUrl = !empty($elem['url']) || !empty($banner->link);
                    $targetUrl = $elem['url'] ?? $banner->link ?? '#';

                    $responsiveClass = '';
                    if (!empty($elem['hideOnMobile'])) {
                        $responsiveClass .= ' hidden sm:block';
                    }
                    if (!empty($elem['hideOnDesktop'])) {
                        $responsiveClass .= ' sm:hidden';
                    }
                @endphp

                <div class="absolute transition-transform duration-200 {{ $responsiveClass }}"
                     style="left: {{ $elem['x'] }}%; top: {{ $elem['y'] }}%; width: {{ $elem['width'] }}%; height: {{ $elem['height'] }}%; z-index: {{ $zIndex }}; transform: {{ $transform }};">

                    @if ($type === 'text')
                        <div style="font-family: {{ $style['fontFamily'] ?? 'Instrument Sans' }}, sans-serif;
                                    font-size: clamp(14px, 3.2vw, {{ $style['fontSize'] ?? 36 }}px);
                                    font-weight: {{ $style['fontWeight'] ?? 700 }};
                                    color: {{ $style['color'] ?? '#ffffff' }};
                                    text-align: {{ $style['textAlign'] ?? 'left' }};
                                    line-height: {{ $style['lineHeight'] ?? 1.15 }};
                                    letter-spacing: {{ $style['letterSpacing'] ?? 0 }}px;
                                    opacity: {{ ($style['opacity'] ?? 100) / 100 }};
                                    word-break: break-word;
                                    width: 100%; height: 100%;">
                            {{ $elem['content'] ?? '' }}
                        </div>

                    @elseif ($type === 'button')
                        <a href="{{ $targetUrl }}"
                           class="inline-flex items-center justify-center font-bold transition-all duration-200 hover:scale-105 hover:brightness-110 shadow-md text-center"
                           style="font-family: {{ $style['fontFamily'] ?? 'Instrument Sans' }}, sans-serif;
                                  font-size: clamp(11px, 1.2vw, {{ $style['fontSize'] ?? 16 }}px);
                                  font-weight: {{ $style['fontWeight'] ?? 600 }};
                                  background-color: {{ $style['backgroundColor'] ?? '#16a34a' }};
                                  color: {{ $style['color'] ?? '#ffffff' }};
                                  border-radius: {{ $style['borderRadius'] ?? 12 }}px;
                                  padding: clamp(6px, 1vw, {{ $style['paddingY'] ?? 12 }}px) clamp(12px, 2vw, {{ $style['paddingX'] ?? 24 }}px);
                                  opacity: {{ ($style['opacity'] ?? 100) / 100 }};
                                  width: 100%; height: 100%;">
                            {{ $elem['content'] ?? 'Shop Now' }}
                        </a>

                    @elseif ($type === 'badge')
                        <div class="inline-flex items-center justify-center font-extrabold uppercase tracking-wider shadow-sm"
                             style="font-family: {{ $style['fontFamily'] ?? 'Instrument Sans' }}, sans-serif;
                                    font-size: clamp(9px, 0.9vw, {{ $style['fontSize'] ?? 14 }}px);
                                    background-color: {{ $style['backgroundColor'] ?? '#ef4444' }};
                                    color: {{ $style['color'] ?? '#ffffff' }};
                                    border-radius: {{ $style['borderRadius'] ?? 9999 }}px;
                                    padding: 4px clamp(8px, 1.2vw, 16px);
                                    opacity: {{ ($style['opacity'] ?? 100) / 100 }};
                                    width: 100%; height: 100%;">
                            {{ $elem['content'] ?? '50% OFF' }}
                        </div>

                    @elseif ($type === 'product')
                        @php
                            $prod = $elem['productData'] ?? [
                                'name' => $elem['content'] ?? 'Supermarket Item',
                                'price' => '₹149.00',
                                'image' => '/images/placeholder.svg',
                                'badge' => 'FEATURED',
                            ];
                            $theme = $style['theme'] ?? 'dark-glass';
                            $cardTheme = 'bg-slate-900/85 backdrop-blur-md border border-white/15 text-white';
                            $badgeTheme = 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
                            $priceTheme = 'text-emerald-400';
                            $titleTheme = 'text-white';

                            if ($theme === 'light-pill') {
                                $cardTheme = 'bg-white/95 backdrop-blur-md border border-slate-200 text-slate-900 shadow-xl';
                                $badgeTheme = 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                                $priceTheme = 'text-emerald-600';
                                $titleTheme = 'text-slate-900';
                            } elseif ($theme === 'flash-deal') {
                                $cardTheme = 'bg-gradient-to-r from-amber-500/25 to-rose-500/25 backdrop-blur-md border border-amber-500/40 text-white shadow-xl';
                                $badgeTheme = 'bg-amber-500/30 text-amber-300 border border-amber-500/50';
                                $priceTheme = 'text-rose-400';
                                $titleTheme = 'text-white';
                            }
                        @endphp
                        <a href="{{ $targetUrl }}"
                           class="flex items-center gap-3 p-3 rounded-2xl shadow-xl transition-all hover:scale-102 h-full w-full overflow-hidden {{ $cardTheme }}"
                           style="border-radius: {{ $style['borderRadius'] ?? 16 }}px; opacity: {{ ($style['opacity'] ?? 100) / 100 }};">
                            <img src="{{ $prod['image'] ?? '/images/placeholder.svg' }}"
                                 alt="{{ $prod['name'] ?? '' }}"
                                 onerror="this.onerror=null;this.src='/images/placeholder.svg';"
                                 class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl object-cover bg-slate-950/20 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $badgeTheme }}">{{ $prod['badge'] ?? 'FEATURED' }}</span>
                                <h4 class="text-xs sm:text-sm font-bold truncate mt-0.5 {{ $titleTheme }}">{{ $prod['name'] ?? '' }}</h4>
                                <span class="text-xs sm:text-base font-extrabold block mt-0.5 {{ $priceTheme }}">{{ $prod['price'] ?? '₹199.00' }}</span>
                            </div>
                        </a>

                    @elseif ($type === 'image')
                        <img src="{{ $elem['url'] ?? '/images/placeholder.svg' }}"
                             alt="{{ $elem['content'] ?? 'Banner Graphic' }}"
                             onerror="this.onerror=null;this.src='/images/placeholder.svg';"
                             style="width: 100%; height: 100%; object-fit: {{ $style['objectFit'] ?? 'cover' }}; border-radius: {{ $style['borderRadius'] ?? 0 }}px; opacity: {{ ($style['opacity'] ?? 100) / 100 }};" />

                    @elseif ($type === 'shape')
                        <div style="width: 100%; height: 100%;
                                    background-color: {{ $style['backgroundColor'] ?? 'rgba(15, 23, 42, 0.75)' }};
                                    border-radius: {{ $style['borderRadius'] ?? 16 }}px;
                                    border: {{ $style['borderWidth'] ?? 1 }}px solid {{ $style['borderColor'] ?? 'rgba(255, 255, 255, 0.15)' }};
                                    backdrop-filter: blur(12px);
                                    opacity: {{ ($style['opacity'] ?? 100) / 100 }};">
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @else
        <!-- Legacy Banner Fallback: Simple Image and Overlay Text -->
        <div class="relative w-full h-full flex items-center px-8 sm:px-12 z-10">
            <div class="max-w-xl">
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight drop-shadow-md">
                    {{ $banner->title }}
                </h2>
                @if (!empty($banner->subtitle))
                    <p class="text-sm sm:text-lg text-slate-200 mt-2 font-medium drop-shadow-sm">
                        {{ $banner->subtitle }}
                    </p>
                @endif
                @if (!empty($banner->link))
                    <a href="{{ $banner->link }}"
                       class="inline-flex items-center gap-2 mt-4 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg transition-all hover:scale-105 text-sm">
                        <span>Explore Now</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    @endif

</div>
