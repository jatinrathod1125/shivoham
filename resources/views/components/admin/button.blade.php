@props([
    'variant' => 'primary', // primary, secondary, outline, danger, success, warning, ghost
    'size' => 'md', // xs, sm, md, lg
    'type' => 'button',
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'loading' => false,
    'disabled' => false,
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-150 focus:outline-hidden focus:ring-2 focus:ring-offset-1 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer select-none';

    $sizeClasses = [
        'xs' => 'text-xs px-2.5 py-1.5 gap-1.5',
        'sm' => 'text-xs px-3 py-2 gap-2',
        'md' => 'text-sm px-4 py-2.5 gap-2',
        'lg' => 'text-base px-5 py-3 gap-2.5',
    ][$size] ?? 'text-sm px-4 py-2.5 gap-2';

    $variantClasses = [
        'primary' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs focus:ring-emerald-500 active:bg-emerald-800',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 shadow-xs focus:ring-slate-400 active:bg-slate-300',
        'outline' => 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-xs hover:border-slate-400 focus:ring-emerald-500',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-xs focus:ring-rose-500 active:bg-rose-800',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs focus:ring-emerald-500',
        'warning' => 'bg-amber-500 hover:bg-amber-600 text-white shadow-xs focus:ring-amber-400',
        'ghost' => 'bg-transparent hover:bg-slate-100 text-slate-600 hover:text-slate-900 focus:ring-slate-300',
    ][$variant] ?? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs focus:ring-emerald-500';

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
    $iconSize = in_array($size, ['xs', 'sm']) ? 'w-3.5 h-3.5' : 'w-4 h-4';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
        <span>{{ $slot }}</span>
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
        <span>{{ $slot }}</span>
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
    </button>
@endif
