@props([
    'name',
    'label' => null,
    'accept' => 'image/*',
    'currentImage' => null,
    'required' => false,
    'hint' => 'PNG, JPG, WEBP up to 2MB',
    'id' => null,
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div class="space-y-1.5 file-upload-wrapper" data-target="{{ $id }}">
    @if($label)
        <label class="block text-xs font-semibold text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-xl transition-colors relative {{ $hasError ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300 hover:border-emerald-500 bg-slate-50/50 hover:bg-emerald-50/20' }}">
        <div class="space-y-2 text-center flex flex-col items-center">
            <div class="file-preview mb-1 {{ $currentImage ? '' : 'hidden' }}">
                <img src="{{ $currentImage ?? '' }}" alt="Preview" class="w-20 h-20 object-cover rounded-lg border border-slate-200 shadow-xs preview-img mx-auto" />
            </div>

            <div class="default-icon {{ $currentImage ? 'hidden' : '' }} w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                <i data-lucide="upload-cloud" class="w-6 h-6"></i>
            </div>

            <div class="flex text-xs text-slate-600">
                <label for="{{ $id }}" class="relative cursor-pointer rounded-md font-medium text-emerald-600 hover:text-emerald-500 focus-within:outline-hidden">
                    <span>Upload a file</span>
                    <input
                        id="{{ $id }}"
                        name="{{ $name }}"
                        type="file"
                        accept="{{ $accept }}"
                        class="sr-only file-upload-input"
                        {{ $required && !$currentImage ? 'required' : '' }}
                        {{ $attributes }}
                    />
                </label>
                <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-[11px] text-slate-400 selected-filename">{{ $hint }}</p>
        </div>
    </div>

    @error($name)
        <p class="text-xs text-rose-600 flex items-center gap-1 mt-1">
            <i data-lucide="alert-circle" class="w-3 h-3"></i>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
