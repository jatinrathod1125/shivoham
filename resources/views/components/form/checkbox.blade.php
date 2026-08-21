@props([
    'name',
    'label',
    'value' => '1',
    'checked' => false,
    'description' => null,
    'disabled' => false,
    'id' => null,
])

@php
    $id = $id ?? $name;
    $isChecked = old($name, $checked);
@endphp

<div class="relative flex items-start">
    <div class="flex h-5 items-center">
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="checkbox"
            value="{{ $value }}"
            {{ $isChecked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'h-4 w-4 rounded-sm border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer disabled:cursor-not-allowed']) }}
        />
    </div>
    <div class="ml-3 text-xs leading-5">
        <label for="{{ $id }}" class="font-medium text-slate-700 select-none cursor-pointer">
            {{ $label }}
        </label>
        @if($description)
            <p class="text-slate-500">{{ $description }}</p>
        @endif
    </div>
</div>
