@props([
    'size' => 'md', // sm, md, lg
    'text' => null,
    'overlay' => false,
])

@php
    $sizeClasses = [
        'sm' => 'w-4 h-4 border-2',
        'md' => 'w-6 h-6 border-2',
        'lg' => 'w-10 h-10 border-3',
    ][$size] ?? 'w-6 h-6 border-2';
@endphp

@if($overlay)
    <div {{ $attributes->merge(['class' => 'absolute inset-0 bg-white/80 backdrop-blur-xs flex flex-col items-center justify-center z-30 rounded-xl transition-all duration-200']) }}>
        <div class="animate-spin rounded-full border-slate-200 border-t-emerald-600 {{ $sizeClasses }}"></div>
        @if($text)
            <p class="text-xs font-medium text-slate-600 mt-2">{{ $text }}</p>
        @endif
    </div>
@else
    <div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
        <div class="animate-spin rounded-full border-slate-200 border-t-emerald-600 {{ $sizeClasses }}"></div>
        @if($text)
            <span class="text-xs text-slate-600">{{ $text }}</span>
        @endif
    </div>
@endif
