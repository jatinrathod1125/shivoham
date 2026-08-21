@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4']) }}>
    <div>
        @if(count($breadcrumbs) > 0)
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                <a href="{{ \Illuminate\Support\Facades\Route::has('admin.dashboard') ? route('admin.dashboard') : url('/') }}" class="hover:text-emerald-600 transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                @foreach($breadcrumbs as $key => $value)
                    @php
                        if (is_array($value)) {
                            $label = $value['title'] ?? $value['label'] ?? $value['name'] ?? '';
                            $url = $value['url'] ?? '';
                        } elseif (is_string($key)) {
                            $label = $key;
                            $url = $value;
                        } else {
                            $label = $value;
                            $url = '';
                        }
                    @endphp
                    <i data-lucide="chevron-right" class="w-3 h-3 text-slate-400"></i>
                    @if($loop->last || empty($url) || $url === '#')
                        <span class="font-medium text-slate-800 truncate max-w-[200px]">{{ $label }}</span>
                    @else
                        <a href="{{ $url }}" class="hover:text-emerald-600 transition-colors truncate max-w-[150px]">{{ $label }}</a>
                    @endif
                @endforeach
            </nav>
        @endif

        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($actions) && $actions->isNotEmpty())
        <div class="flex items-center flex-wrap gap-2.5">
            {{ $actions }}
        </div>
    @elseif($slot->isNotEmpty())
        <div class="flex items-center flex-wrap gap-2.5">
            {{ $slot }}
        </div>
    @endif
</div>
