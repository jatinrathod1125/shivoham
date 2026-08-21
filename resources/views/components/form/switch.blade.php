@props([
    'name',
    'label' => null,
    'checked' => false,
    'description' => null,
    'disabled' => false,
    'id' => null,
])

@php
    $id = $id ?? $name;
    $isChecked = (bool) old($name, $checked);
@endphp

<div class="flex items-center justify-between gap-4">
    @if($label || $description)
        <div class="space-y-0.5">
            @if($label)
                <label for="{{ $id }}" class="text-xs font-semibold text-slate-700 cursor-pointer select-none">
                    {{ $label }}
                </label>
            @endif
            @if($description)
                <p class="text-xs text-slate-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    <label class="relative inline-flex items-center cursor-pointer shrink-0">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $id }}"
            value="1"
            {{ $isChecked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="sr-only peer"
        />
        <div class="w-10 h-5.5 bg-slate-200 peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:bg-emerald-600"></div>
    </label>
</div>
