@props([
    'align' => 'right', // left, right
    'width' => '48', // 48, 56, 64, full
])

@php
    $alignmentClasses = [
        'left' => 'left-0 origin-top-left',
        'right' => 'right-0 origin-top-right',
    ][$align] ?? 'right-0 origin-top-right';

    $widthClasses = [
        '40' => 'w-40',
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        'full' => 'w-full',
    ][$width] ?? 'w-48';
@endphp

<div class="relative inline-block text-left dropdown-container">
    <div class="dropdown-trigger cursor-pointer" onclick="if(window.Admin && typeof window.Admin.toggleDropdown === 'function') { window.Admin.toggleDropdown(this, event); }">
        {{ $trigger }}
    </div>

    <div class="dropdown-menu hidden absolute z-40 mt-2 {{ $widthClasses }} {{ $alignmentClasses }} rounded-xl bg-white shadow-lg border border-slate-100 ring-1 ring-black/5 focus:outline-hidden py-1 transition-all duration-150 transform opacity-0 scale-95">
        {{ $content }}
    </div>
</div>
