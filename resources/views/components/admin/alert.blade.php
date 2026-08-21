@props([
    'type' => 'info', // success, error, warning, info
    'title' => null,
    'dismissible' => true,
])

@php
    $typeConfig = [
        'success' => [
            'classes' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'icon' => 'check-circle-2',
            'iconColor' => 'text-emerald-500',
        ],
        'error' => [
            'classes' => 'bg-rose-50 text-rose-800 border-rose-200',
            'icon' => 'alert-circle',
            'iconColor' => 'text-rose-500',
        ],
        'warning' => [
            'classes' => 'bg-amber-50 text-amber-800 border-amber-200',
            'icon' => 'alert-triangle',
            'iconColor' => 'text-amber-500',
        ],
        'info' => [
            'classes' => 'bg-sky-50 text-sky-800 border-sky-200',
            'icon' => 'info',
            'iconColor' => 'text-sky-500',
        ],
    ][$type] ?? [
        'classes' => 'bg-slate-50 text-slate-800 border-slate-200',
        'icon' => 'info',
        'iconColor' => 'text-slate-500',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 p-4 rounded-xl border ' . $typeConfig['classes']]) }} role="alert">
    <i data-lucide="{{ $typeConfig['icon'] }}" class="w-5 h-5 shrink-0 mt-0.5 {{ $typeConfig['iconColor'] }}"></i>
    <div class="flex-1 text-sm">
        @if($title)
            <div class="font-semibold mb-0.5">{{ $title }}</div>
        @endif
        <div>{{ $slot }}</div>
    </div>
    @if($dismissible)
        <button type="button" onclick="$(this).closest('[role=alert]').fadeOut(200, function(){ $(this).remove(); })" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    @endif
</div>
