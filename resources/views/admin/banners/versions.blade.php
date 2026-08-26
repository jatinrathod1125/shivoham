@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        :title="'Version History: ' . $banner->title"
        subtitle="Review historical snapshots, audit past content edits, and restore previous versions with one click."
        :breadcrumbs="[
            ['title' => 'Banners', 'url' => route('admin.banners.index')],
            ['title' => $banner->title, 'url' => route('admin.banners.editor', $banner->id)],
            ['title' => 'Version History', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.banners.editor', $banner->id)"
                variant="secondary"
                size="sm"
                icon="arrow-left"
            >
                Back to Editor
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                        <i data-lucide="history" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Campaign Version Snapshots</h2>
                        <p class="text-xs text-slate-500">Every published modification creates an immutable snapshot.</p>
                    </div>
                </div>

                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                    {{ $versions->count() }} Total Versions
                </span>
            </div>

            <!-- Version Timeline -->
            <div class="space-y-4">
                @foreach ($versions as $version)
                    @php
                        $isCurrent = $banner->active_version_id === $version->id;
                    @endphp

                    <div class="p-4 rounded-xl border {{ $isCurrent ? 'border-emerald-500/50 bg-emerald-50/20' : 'border-slate-200/80 bg-white hover:border-slate-300' }} transition-colors flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-xl {{ $isCurrent ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center text-xs font-black shrink-0">
                                v{{ $version->version_number }}
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xs font-bold text-slate-900">
                                        Version {{ $version->version_number }}
                                    </h3>

                                    @if ($isCurrent)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            Currently Live
                                        </span>
                                    @elseif ($version->status === 'published')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                            Previously Published
                                        </span>
                                    @elseif ($version->status === 'draft')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Draft
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-600">
                                    {{ $version->change_summary ?: 'Content modification' }}
                                </p>

                                <div class="flex items-center gap-3 text-[11px] text-slate-400">
                                    <span>Created {{ $version->created_at->diffForHumans() }}</span>
                                    @if ($version->published_at)
                                        <span>&bull; Published {{ $version->published_at->format('M d, Y H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            @if (!$isCurrent)
                                <form action="{{ route('admin.banners.rollback', ['banner' => $banner->id, 'version' => $version->id]) }}" method="POST">
                                    @csrf
                                    <button
                                        type="submit"
                                        onclick="return confirm('Rollback banner content to v{{ $version->version_number }}?')"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:text-emerald-700 transition-colors flex items-center gap-1.5 cursor-pointer"
                                    >
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        <span>Restore Version</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
