@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'icon' => null,
    'iconRight' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'hint' => null,
    'id' => null,
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
    $inputValue = old($name, $value);

    $inputClasses = 'w-full rounded-lg text-sm bg-white border transition-colors duration-150 placeholder:text-slate-400 focus:outline-hidden focus:ring-2 disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed ' .
        ($hasError ? 'border-rose-300 text-rose-900 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 text-slate-900 hover:border-slate-400 focus:border-emerald-500 focus:ring-emerald-500/20') .
        ($icon ? ' pl-10' : ' pl-3.5') .
        ($iconRight ? ' pr-10' : ' pr-3.5') .
        ' py-2.5';
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id }}"
            value="{{ $inputValue }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $attributes->merge(['class' => $inputClasses]) }}
        />

        @if($iconRight)
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                <i data-lucide="{{ $iconRight }}" class="w-4 h-4"></i>
            </div>
        @endif
    </div>

    @if($hint && !$hasError)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-rose-600 flex items-center gap-1 mt-1">
            <i data-lucide="alert-circle" class="w-3 h-3"></i>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
