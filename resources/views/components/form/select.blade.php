@props([
    'name',
    'label' => null,
    'options' => [], // ['val' => 'Label'] or simple array
    'selected' => null,
    'placeholder' => 'Select an option',
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'id' => null,
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
    $currentValue = old($name, $selected);

    $selectClasses = 'w-full rounded-lg text-sm bg-white border transition-colors duration-150 focus:outline-hidden focus:ring-2 disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed pl-3.5 pr-10 py-2.5 appearance-none cursor-pointer ' .
        ($hasError ? 'border-rose-300 text-rose-900 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 text-slate-900 hover:border-slate-400 focus:border-emerald-500 focus:ring-emerald-500/20');
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
        <select
            name="{{ $name }}"
            id="{{ $id }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => $selectClasses]) }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif

            @if(count($options) > 0)
                @foreach($options as $key => $labelValue)
                    @php
                        $optionVal = is_numeric($key) && !is_string($key) ? $labelValue : $key;
                        $isSelected = (string)$currentValue === (string)$optionVal;
                    @endphp
                    <option value="{{ $optionVal }}" {{ $isSelected ? 'selected' : '' }}>
                        {{ $labelValue }}
                    </option>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>

        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
            <i data-lucide="chevron-down" class="w-4 h-4"></i>
        </div>
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
