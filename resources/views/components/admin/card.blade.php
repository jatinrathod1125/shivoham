@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'padding' => true,
    'footer' => null,
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/80 shadow-xs transition-shadow duration-200']) }}>
    @if($title || $actions || $subtitle)
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
            <div class="min-w-0">
                @if($title)
                    <div class="flex items-center gap-2">
                        @if($icon)
                            <i data-lucide="{{ $icon }}" class="w-4 h-4 text-emerald-600"></i>
                        @endif
                        <h3 class="text-base font-semibold text-slate-900 truncate">{{ $title }}</h3>
                    </div>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actions)
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding ? 'p-5' : '' }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-5 py-3.5 bg-slate-50/70 border-t border-slate-100 rounded-b-xl">
            {{ $footer }}
        </div>
    @endif
</div>
