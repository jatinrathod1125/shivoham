@props([
    'banners' => [],
    'position' => 'home_hero',
    'class' => '',
])

@php
    if ($banners instanceof \Illuminate\Support\Collection) {
        $bannerList = $banners;
    } elseif (empty($banners)) {
        $bannerList = \App\Models\Banner::getActiveByPosition($position);
    } else {
        $bannerList = collect($banners);
    }
@endphp

@if ($bannerList->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'storefront-banner-slider-container relative w-full ' . $class]) }}>
        @if ($bannerList->count() === 1)
            <!-- Single Visual Banner -->
            <x-storefront-banner :banner="$bannerList->first()" />
        @else
            <!-- Multi-Banner Interactive Slider -->
            <div class="relative overflow-hidden rounded-2xl group" id="storefront-carousel-{{ $position }}" role="region" aria-roledescription="carousel" aria-label="Promotional Banners Carousel">
                <div class="banner-slides-track flex transition-transform duration-500 ease-out" id="track-{{ $position }}">
                    @foreach ($bannerList as $idx => $b)
                        <div class="min-w-full flex-shrink-0" role="group" aria-roledescription="slide" aria-label="Slide {{ $idx + 1 }} of {{ $bannerList->count() }}">
                            <x-storefront-banner :banner="$b" />
                        </div>
                    @endforeach
                </div>

                <!-- Navigation Chevrons -->
                <button type="button"
                        onclick="prevBannerSlide('{{ $position }}', {{ $bannerList->count() }})"
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-slate-950/60 hover:bg-slate-950/90 text-white flex items-center justify-center backdrop-blur-md border border-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-20 shadow-lg"
                        title="Previous Banner"
                        aria-label="Previous Banner Slide">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <button type="button"
                        onclick="nextBannerSlide('{{ $position }}', {{ $bannerList->count() }})"
                        class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-slate-950/60 hover:bg-slate-950/90 text-white flex items-center justify-center backdrop-blur-md border border-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-20 shadow-lg"
                        title="Next Banner"
                        aria-label="Next Banner Slide">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Dots Pagination Indicator -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20 bg-slate-950/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                    @for ($i = 0; $i < $bannerList->count(); $i++)
                        <button type="button"
                                onclick="goToBannerSlide('{{ $position }}', {{ $i }})"
                                class="banner-dot-{{ $position }} h-2 rounded-full transition-all duration-300 {{ $i === 0 ? 'w-6 bg-emerald-400' : 'w-2 bg-white/60 hover:bg-white' }}"
                                data-slide-index="{{ $i }}"
                                title="Slide {{ $i + 1 }}"></button>
                    @endfor
                </div>
            </div>

            <!-- Auto-Rotation & Slide Script -->
            <script>
                (function() {
                    let currentSlide = 0;
                    const pos = '{{ $position }}';
                    const total = {{ $bannerList->count() }};

                    function updateSlideUI() {
                        const track = document.getElementById('track-' + pos);
                        if (track) {
                            track.style.transform = `translateX(-${currentSlide * 100}%)`;
                        }
                        const dots = document.querySelectorAll('.banner-dot-' + pos);
                        dots.forEach((dot, idx) => {
                            if (idx === currentSlide) {
                                dot.classList.add('w-6', 'bg-emerald-400');
                                dot.classList.remove('w-2', 'bg-white/60');
                            } else {
                                dot.classList.remove('w-6', 'bg-emerald-400');
                                dot.classList.add('w-2', 'bg-white/60');
                            }
                        });
                    }

                    window.nextBannerSlide = function(p, t) {
                        if (p !== pos) return;
                        currentSlide = (currentSlide + 1) % t;
                        updateSlideUI();
                    };

                    window.prevBannerSlide = function(p, t) {
                        if (p !== pos) return;
                        currentSlide = (currentSlide - 1 + t) % t;
                        updateSlideUI();
                    };

                    window.goToBannerSlide = function(p, index) {
                        if (p !== pos) return;
                        currentSlide = index;
                        updateSlideUI();
                    };

                    // Auto advance every 6 seconds
                    setInterval(function() {
                        currentSlide = (currentSlide + 1) % total;
                        updateSlideUI();
                    }, 6000);
                })();
            </script>
        @endif
    </div>
@endif
