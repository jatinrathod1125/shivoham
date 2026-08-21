@props([
    'icon' => 'inbox',
    'title' => 'No records found',
    'description' => 'There are currently no records available in this section.',
    'actionText' => null,
    'actionUrl' => null,
    'actionIcon' => 'plus',
])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4 rounded-xl border-2 border-dashed border-slate-200 bg-white/50']) }}>
    <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100/80 text-emerald-600 flex items-center justify-center mx-auto mb-4">
        <i data-lucide="{{ $icon }}" class="w-7 h-7"></i>
    </div>
    <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-5">{{ $description }}</p>

    @if($actionText && $actionUrl)
        <x-admin.button :href="$actionUrl" variant="primary" size="sm" :icon="$actionIcon">
            {{ $actionText }}
        </x-admin.button>
    @elseif($slot->isNotEmpty())
        <div>{{ $slot }}</div>
    @endif
</div>
