@props([
    'variant' => 'neutral', // success, warning, danger, info, neutral, primary, purple, amber
    'size' => 'md', // sm, md
    'dot' => false,
])

@php
    $baseClasses = 'inline-flex items-center font-medium rounded-full';

    $sizeClasses = [
        'sm' => 'text-[11px] px-2 py-0.5 gap-1.5',
        'md' => 'text-xs px-2.5 py-1 gap-1.5',
    ][$size] ?? 'text-xs px-2.5 py-1 gap-1.5';

    $variantClasses = [
        'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
        'warning' => 'bg-amber-50 text-amber-700 border border-amber-200/80',
        'danger' => 'bg-rose-50 text-rose-700 border border-rose-200/80',
        'info' => 'bg-sky-50 text-sky-700 border border-sky-200/80',
        'primary' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'purple' => 'bg-purple-50 text-purple-700 border border-purple-200/80',
        'amber' => 'bg-orange-50 text-orange-700 border border-orange-200/80',
        'neutral' => 'bg-slate-100 text-slate-700 border border-slate-200',
    ][$variant] ?? 'bg-slate-100 text-slate-700 border border-slate-200';

    $dotColors = [
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'info' => 'bg-sky-500',
        'primary' => 'bg-emerald-500',
        'purple' => 'bg-purple-500',
        'amber' => 'bg-orange-500',
        'neutral' => 'bg-slate-400',
    ][$variant] ?? 'bg-slate-400';

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors }}"></span>
    @endif
    {{ $slot }}
</span>
