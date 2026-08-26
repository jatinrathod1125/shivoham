@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        :title="'Dynamic Banner: ' . $banner->title"
        subtitle="Edit dynamic text, pricing, media, and call-to-action fields. Original layout, animations, and responsive rules remain locked and preserved."
        :breadcrumbs="[
            ['title' => 'Banners', 'url' => route('admin.banners.index')],
            ['title' => 'Dynamic Content Editor', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.banners.versions', $banner->id) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors flex items-center gap-2"
                >
                    <i data-lucide="history" class="w-4 h-4 text-purple-600"></i>
                    <span>Version History (v{{ $version?->version_number ?? 1 }})</span>
                </a>

                <form action="{{ route('admin.banners.reanalyze', $banner->id) }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors flex items-center gap-2 cursor-pointer"
                        title="Re-run AI structural analysis"
                    >
                        <i data-lucide="sparkles" class="w-4 h-4 text-emerald-600"></i>
                        <span>Re-Analyze AI</span>
                    </button>
                </form>

                <x-admin.button
                    :href="route('admin.banners.index')"
                    variant="secondary"
                    size="sm"
                    icon="arrow-left"
                >
                    Back to Banners
                </x-admin.button>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- AI Analysis Summary Card -->
    @if ($analysis)
        <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-950/80 via-slate-900 to-slate-950 border border-emerald-500/30 text-white shadow-md flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center font-bold shrink-0">
                    <i data-lucide="check-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Design Analyzed Successfully</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            {{ round($analysis->overall_confidence * 100) }}% Confidence
                        </span>
                    </div>
                    <p class="text-xs text-slate-300 mt-0.5">
                        <strong class="text-white">{{ $analysis->elements_detected_count }}</strong> elements detected &bull;
                        <strong class="text-emerald-400">{{ $analysis->editable_elements_count }}</strong> editable content slots &bull;
                        <strong class="text-slate-400">{{ $analysis->locked_elements_count }}</strong> locked design layers
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-400"></i>
                <span>Design & Animations Locked</span>
            </div>
        </div>
    @endif

    <!-- Split Screen: Left (Content Fields) & Right (Live Preview) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column: Content Fields Form (lg:col-span-5) -->
        <div class="lg:col-span-5 space-y-6">
            <form
                action="{{ route('admin.banners.update-fields', $banner->id) }}"
                method="POST"
                enctype="multipart/form-data"
                id="dynamic-content-form"
                class="space-y-6"
            >
                @csrf

                <!-- Card 1: Core Banner Campaign Info -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2.5">
                        Campaign Settings
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" for="campaign-title">
                            Campaign Name
                        </label>
                        <input
                            type="text"
                            id="campaign-title"
                            name="title"
                            value="{{ old('title', $banner->title) }}"
                            required
                            class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 outline-hidden"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1" for="campaign-position">
                                Placement
                            </label>
                            <select
                                id="campaign-position"
                                name="position"
                                class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 outline-hidden"
                            >
                                <option value="home_hero" {{ $banner->position === 'home_hero' ? 'selected' : '' }}>Home Hero</option>
                                <option value="promotional_bar" {{ $banner->position === 'promotional_bar' ? 'selected' : '' }}>Promo Bar</option>
                                <option value="category_top" {{ $banner->position === 'category_top' ? 'selected' : '' }}>Category Header</option>
                                <option value="sidebar" {{ $banner->position === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                                <option value="popup" {{ $banner->position === 'popup' ? 'selected' : '' }}>Popup</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1" for="campaign-active">
                                Status
                            </label>
                            <select
                                id="campaign-active"
                                name="is_active"
                                class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 outline-hidden"
                            >
                                <option value="1" {{ $banner->is_active ? 'selected' : '' }}>Active (Visible)</option>
                                <option value="0" {{ !$banner->is_active ? 'selected' : '' }}>Inactive (Draft)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Dynamic Content Fields -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Dynamic Content Fields
                        </h3>
                        <span class="text-[11px] font-semibold text-emerald-600">
                            {{ $fields->where('is_editable', true)->count() }} Editable Slots
                        </span>
                    </div>

                    <div class="space-y-4">
                        @foreach ($fields as $field)
                            @php
                                $val = $fieldValues[$field->field_key] ?? $field->default_value;
                                $isEditable = $field->is_editable;
                            @endphp

                            <div class="p-3.5 rounded-xl border {{ $isEditable ? 'border-slate-200/80 bg-white hover:border-emerald-300' : 'border-slate-100 bg-slate-50/70 opacity-70' }} transition-colors space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase {{ $isEditable ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-200 text-slate-600' }}">
                                            {{ str_replace('_', ' ', $field->semantic_role) }}
                                        </span>
                                        <label class="text-xs font-bold text-slate-800">
                                            {{ $field->label }}
                                        </label>
                                    </div>

                                    <!-- Quick Role Correction Button -->
                                    <button
                                        type="button"
                                        onclick="openRoleCorrectionModal('{{ $field->id }}', '{{ $field->field_key }}', '{{ $field->semantic_role }}', '{{ $field->label }}')"
                                        class="text-[10px] text-slate-400 hover:text-emerald-600 flex items-center gap-1 font-medium transition-colors cursor-pointer"
                                        title="Adjust role assignment"
                                    >
                                        <i data-lucide="sliders" class="w-3 h-3"></i>
                                        <span>Change Role</span>
                                    </button>
                                </div>

                                @if ($isEditable)
                                    <!-- Input depending on field type -->
                                    @if ($field->field_type === 'image')
                                        <div class="space-y-2">
                                            <input
                                                type="text"
                                                name="fields[{{ $field->field_key }}]"
                                                value="{{ is_array($val) ? ($val['url'] ?? '') : $val }}"
                                                placeholder="https://... or upload below"
                                                oninput="refreshPreview()"
                                                class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 outline-hidden"
                                            />
                                            <div class="flex items-center gap-2">
                                                <input
                                                    type="file"
                                                    name="files[{{ $field->field_key }}]"
                                                    accept="image/*"
                                                    class="text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer"
                                                />
                                            </div>
                                        </div>
                                    @elseif ($field->field_type === 'cta')
                                        <div class="space-y-2">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <span class="text-[10px] text-slate-400 font-medium">Button Label</span>
                                                    <input
                                                        type="text"
                                                        name="fields[{{ $field->field_key }}][text]"
                                                        value="{{ is_array($val) ? ($val['text'] ?? '') : $val }}"
                                                        placeholder="e.g. Shop Deals"
                                                        oninput="refreshPreview()"
                                                        class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 outline-hidden"
                                                    />
                                                </div>
                                                <div>
                                                    <span class="text-[10px] text-slate-400 font-medium">Target URL</span>
                                                    <input
                                                        type="text"
                                                        name="fields[{{ $field->field_key }}][url]"
                                                        value="{{ is_array($val) ? ($val['url'] ?? '') : '/deals' }}"
                                                        placeholder="/catalog/..."
                                                        oninput="refreshPreview()"
                                                        class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 outline-hidden"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    @elseif (in_array($field->semantic_role, ['subtitle', 'description']) && strlen($val ?? '') > 60)
                                        <textarea
                                            name="fields[{{ $field->field_key }}]"
                                            rows="2"
                                            oninput="refreshPreview()"
                                            class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 outline-hidden"
                                        >{{ $val }}</textarea>
                                    @else
                                        <input
                                            type="text"
                                            name="fields[{{ $field->field_key }}]"
                                            value="{{ is_array($val) ? json_encode($val) : $val }}"
                                            oninput="refreshPreview()"
                                            class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 outline-hidden"
                                        />
                                    @endif
                                @else
                                    <div class="text-xs text-slate-500 italic flex items-center gap-1.5">
                                        <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i>
                                        <span>Locked design element ({{ $field->default_value ?: 'Visual Layer' }})</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Save Action -->
                <div class="sticky bottom-4 z-20 p-4 rounded-2xl bg-white/95 backdrop-blur-md border border-slate-200 shadow-lg flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">Ready to publish changes?</span>
                    <button
                        type="submit"
                        class="px-6 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-sm shadow-emerald-900/20 hover:bg-emerald-700 transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Save & Publish Version</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Live Multi-Device Sandboxed Preview (lg:col-span-7) -->
        <div class="lg:col-span-7 space-y-4 lg:sticky lg:top-6">
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        <h3 class="text-xs font-bold text-slate-900">Live Sandboxed Preview</h3>
                    </div>

                    <!-- Device Breakpoint Toggles -->
                    <div class="flex items-center bg-slate-100 p-1 rounded-xl gap-1 text-xs font-bold">
                        <button
                            type="button"
                            onclick="setPreviewViewport('desktop')"
                            id="btn-vp-desktop"
                            class="px-3 py-1.5 rounded-lg bg-white text-emerald-700 shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                        >
                            <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
                            <span>Desktop</span>
                        </button>

                        <button
                            type="button"
                            onclick="setPreviewViewport('tablet')"
                            id="btn-vp-tablet"
                            class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 flex items-center gap-1.5 transition-all cursor-pointer"
                        >
                            <i data-lucide="tablet" class="w-3.5 h-3.5"></i>
                            <span>Tablet</span>
                        </button>

                        <button
                            type="button"
                            onclick="setPreviewViewport('mobile')"
                            id="btn-vp-mobile"
                            class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 flex items-center gap-1.5 transition-all cursor-pointer"
                        >
                            <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                            <span>Mobile</span>
                        </button>
                    </div>
                </div>

                <!-- Preview Frame Container -->
                <div class="w-full bg-slate-950/5 rounded-2xl p-3 flex justify-center items-center overflow-x-auto min-h-[480px]">
                    <div id="preview-viewport-box" class="w-full transition-all duration-300 shadow-md rounded-xl overflow-hidden bg-white">
                        <iframe
                            id="banner-live-iframe"
                            class="w-full h-[460px] border-0"
                            sandbox="allow-scripts allow-same-origin"
                            src="{{ route('admin.banners.preview', $banner->id) }}"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Correction Modal (No Code Editor) -->
    <div id="role-correction-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl border border-slate-200 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Semantic Role Correction</h3>
                </div>
                <button type="button" onclick="closeRoleCorrectionModal()" class="text-slate-400 hover:text-slate-700 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="role-correction-form" onsubmit="submitRoleCorrection(event)" class="space-y-4">
                <input type="hidden" id="modal-field-id" />

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Target Element</label>
                    <p id="modal-element-label" class="text-xs font-bold text-slate-900 bg-slate-50 p-2.5 rounded-xl border border-slate-200"></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1" for="modal-role-select">Select Semantic Role</label>
                    <select
                        id="modal-role-select"
                        class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 outline-hidden font-medium"
                    >
                        @foreach ($roles as $roleKey => $roleDef)
                            <option value="{{ $roleKey }}">{{ $roleDef['label'] }} ({{ $roleKey }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="modal-editable-check" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4" checked />
                    <label for="modal-editable-check" class="text-xs font-bold text-slate-700">Make this element editable in admin panel</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        onclick="closeRoleCorrectionModal()"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors shadow-xs cursor-pointer"
                    >
                        Apply Semantic Correction
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function setPreviewViewport(viewport) {
            var box = document.getElementById('preview-viewport-box');
            var btnDesktop = document.getElementById('btn-vp-desktop');
            var btnTablet = document.getElementById('btn-vp-tablet');
            var btnMobile = document.getElementById('btn-vp-mobile');

            btnDesktop.className = 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 flex items-center gap-1.5 transition-all cursor-pointer';
            btnTablet.className = 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 flex items-center gap-1.5 transition-all cursor-pointer';
            btnMobile.className = 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 flex items-center gap-1.5 transition-all cursor-pointer';

            if (viewport === 'mobile') {
                box.style.maxWidth = '375px';
                btnMobile.className = 'px-3 py-1.5 rounded-lg bg-white text-emerald-700 shadow-xs flex items-center gap-1.5 transition-all cursor-pointer';
            } else if (viewport === 'tablet') {
                box.style.maxWidth = '768px';
                btnTablet.className = 'px-3 py-1.5 rounded-lg bg-white text-emerald-700 shadow-xs flex items-center gap-1.5 transition-all cursor-pointer';
            } else {
                box.style.maxWidth = '100%';
                btnDesktop.className = 'px-3 py-1.5 rounded-lg bg-white text-emerald-700 shadow-xs flex items-center gap-1.5 transition-all cursor-pointer';
            }
        }

        var previewDebounceTimer = null;
        function refreshPreview() {
            clearTimeout(previewDebounceTimer);
            previewDebounceTimer = setTimeout(function() {
                var iframe = document.getElementById('banner-live-iframe');
                if (iframe) {
                    iframe.src = "{{ route('admin.banners.preview', $banner->id) }}?t=" + Date.now();
                }
            }, 500);
        }

        function openRoleCorrectionModal(fieldId, fieldKey, currentRole, label) {
            document.getElementById('modal-field-id').value = fieldId;
            document.getElementById('modal-element-label').textContent = label + ' (' + fieldKey + ')';
            document.getElementById('modal-role-select').value = currentRole;
            document.getElementById('role-correction-modal').classList.remove('hidden');
        }

        function closeRoleCorrectionModal() {
            document.getElementById('role-correction-modal').classList.add('hidden');
        }

        function submitRoleCorrection(e) {
            e.preventDefault();
            var fieldId = document.getElementById('modal-field-id').value;
            var role = document.getElementById('modal-role-select').value;
            var isEditable = document.getElementById('modal-editable-check').checked ? 1 : 0;

            fetch('{{ url("admin/banners/{$banner->id}/fields") }}/' + fieldId + '/role', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    semantic_role: role,
                    is_editable: isEditable
                })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    closeRoleCorrectionModal();
                    window.location.reload();
                }
            })
            .catch(function(err) {
                console.error(err);
                alert('Failed to update role.');
            });
        }
    </script>
@endpush
