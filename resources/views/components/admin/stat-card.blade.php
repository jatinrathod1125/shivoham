@props([
    'title',
    'value',
    'icon',
    'iconColor' => 'emerald', // emerald, blue, amber, purple, rose
    'trend' => null, // e.g. "+12.5%" or "-3.2%"
    'trendUp' => true,
    'timeframe' => 'vs last month',
    'badge' => null,
])

@php
    $iconThemes = [
        'emerald' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
        'blue' => 'bg-sky-50 text-sky-600 border border-sky-100',
        'amber' => 'bg-amber-50 text-amber-600 border border-amber-100',
        'purple' => 'bg-purple-50 text-purple-600 border border-purple-100',
        'rose' => 'bg-rose-50 text-rose-600 border border-rose-100',
    ][$iconColor] ?? 'bg-emerald-50 text-emerald-600 border border-emerald-100';
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between']) }}>
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ $title }}</span>
            <div class="text-2xl font-bold text-slate-900 tracking-tight">{{ $value }}</div>
        </div>
        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $iconThemes }}">
            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
        </div>
    </div>

    @if($trend !== null || $badge !== null)
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            @if($trend !== null)
                <div class="flex items-center gap-1.5 font-medium {{ $trendUp ? 'text-emerald-600' : 'text-rose-600' }}">
                    <i data-lucide="{{ $trendUp ? 'trending-up' : 'trending-down' }}" class="w-3.5 h-3.5"></i>
                    <span>{{ $trend }}</span>
                    <span class="text-slate-400 font-normal ml-1">{{ $timeframe }}</span>
                </div>
            @endif
            @if($badge !== null)
                <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">{{ $badge }}</span>
            @endif
        </div>
    @endif
</div>
