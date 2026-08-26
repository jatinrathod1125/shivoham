@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="Universal AI Banner Import"
        subtitle="Import any ready-made banner design (ZIP package, raw code, or flattened image). The AI will analyze the structure and automatically expose editable content fields."
        :breadcrumbs="[
            ['title' => 'Banners', 'url' => route('admin.banners.index')],
            ['title' => 'Import Design', 'url' => '']
        ]"
    >
        <x-slot:actions>
            <x-admin.button
                :href="route('admin.banners.index')"
                variant="secondary"
                size="sm"
                icon="arrow-left"
            >
                Back to Banners
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="max-w-4xl mx-auto">
        <form
            action="{{ route('admin.banners.import.process') }}"
            method="POST"
            enctype="multipart/form-data"
            id="banner-import-form"
            class="space-y-6"
        >
            @csrf

            <!-- Card 1: Banner Basic Information -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-5">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Campaign Details</h2>
                        <p class="text-xs text-slate-500">Provide the title and target storefront placement for this dynamic banner.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5" for="banner-title">
                            Banner Campaign Name <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="banner-title"
                            name="title"
                            value="{{ old('title', 'Summer Organic Harvest') }}"
                            required
                            placeholder="e.g. Summer Mega Sale Hero"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-hidden text-slate-800"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5" for="banner-position">
                            Placement Position <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="banner-position"
                            name="position"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-hidden text-slate-800"
                        >
                            <option value="home_hero" selected>Home Hero Slider (Top)</option>
                            <option value="promotional_bar">Promotional Announcement Bar</option>
                            <option value="category_top">Category Page Header</option>
                            <option value="sidebar">Sidebar Promo Card</option>
                            <option value="popup">Modal Announcement Popup</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Card 2: Import Mode Selection & Package Upload -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-6">
                <div class="flex flex-wrap items-center justify-between border-b border-slate-100 pb-4 gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                            <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Design Package Input</h2>
                            <p class="text-xs text-slate-500">Choose your preferred import format. Original design and styling will be preserved.</p>
                        </div>
                    </div>

                    <!-- Mode Toggle Pill (PSD, ZIP, Code) -->
                    <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs font-bold text-slate-600">
                        <button
                            type="button"
                            id="tab-psd-btn"
                            onclick="switchImportMode('psd')"
                            class="px-3.5 py-1.5 rounded-lg transition-all bg-white text-sky-700 shadow-xs cursor-pointer flex items-center gap-1.5"
                        >
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-sky-600"></i>
                            <span>Photoshop (.PSD)</span>
                        </button>
                        <button
                            type="button"
                            id="tab-zip-btn"
                            onclick="switchImportMode('zip')"
                            class="px-3.5 py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-800 cursor-pointer flex items-center gap-1.5"
                        >
                            <i data-lucide="file-archive" class="w-3.5 h-3.5"></i>
                            <span>ZIP Package</span>
                        </button>
                        <button
                            type="button"
                            id="tab-code-btn"
                            onclick="switchImportMode('raw_code')"
                            class="px-3.5 py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-800 cursor-pointer flex items-center gap-1.5"
                        >
                            <i data-lucide="code" class="w-3.5 h-3.5"></i>
                            <span>Paste HTML/CSS</span>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="import_type" id="import_type_input" value="psd" />

                <!-- Mode 1: Photoshop (.PSD) Layered Upload (Up to 500MB) -->
                <div id="section-psd-mode" class="space-y-4">
                    <div class="border-2 border-dashed border-sky-300 hover:border-sky-500 rounded-2xl p-8 text-center transition-colors bg-sky-50/30 hover:bg-sky-50/60 relative">
                        <div class="absolute top-4 right-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wider">
                                Max Size: 500MB
                            </span>
                        </div>

                        <div class="w-16 h-16 rounded-2xl bg-white border border-sky-200 shadow-sm flex items-center justify-center mx-auto text-sky-600 mb-3">
                            <i data-lucide="layers" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-1">Upload Layered Photoshop (.PSD) Banner</h3>
                        <p class="text-xs text-slate-500 max-w-lg mx-auto mb-4 leading-relaxed">
                            Upload your native Photoshop <code class="bg-sky-100 text-sky-800 font-mono px-1.5 py-0.5 rounded text-[11px]">.psd</code> file (up to 500MB). The engine extracts typography, decomposes raster image layers into PNGs, and reconstructs a responsive dynamic design.
                        </p>
                        <label
                            for="psd_file"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-sky-600 text-white shadow-xs hover:bg-sky-700 cursor-pointer transition-colors"
                        >
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            <span>Browse Photoshop (.PSD) File</span>
                        </label>
                        <input
                            type="file"
                            name="psd_file"
                            id="psd_file"
                            accept=".psd,application/x-photoshop,image/vnd.adobe.photoshop,image/photoshop,image/x-photoshop"
                            class="hidden"
                            onchange="handleFileSelected(this, 'psd')"
                        />
                        <div id="psd-filename-preview" class="hidden mt-4 text-xs font-bold text-sky-800 flex items-center justify-center gap-2 bg-white/80 py-2 px-4 rounded-xl border border-sky-200 w-fit mx-auto shadow-2xs">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                            <span id="psd-filename-text"></span>
                        </div>
                    </div>
                </div>

                <!-- Mode 2: ZIP Archive Upload -->
                <div id="section-zip-mode" class="hidden space-y-4">
                    <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-8 text-center transition-colors bg-slate-50/50 hover:bg-emerald-50/20 relative">
                        <div class="absolute top-4 right-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                Max Size: 500MB
                            </span>
                        </div>

                        <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-center mx-auto text-emerald-600 mb-3">
                            <i data-lucide="file-archive" class="w-7 h-7"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 mb-1">Upload Banner ZIP Package</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mb-4">
                            Upload a ZIP archive containing <code class="bg-slate-200/80 px-1 py-0.5 rounded text-[11px]">index.html</code>, stylesheets, scripts, and asset folders.
                        </p>
                        <label
                            for="zip_file"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-xs hover:bg-emerald-700 cursor-pointer transition-colors"
                        >
                            <i data-lucide="folder-search" class="w-4 h-4"></i>
                            <span>Browse ZIP Archive</span>
                        </label>
                        <input
                            type="file"
                            name="zip_file"
                            id="zip_file"
                            accept=".zip"
                            class="hidden"
                            onchange="handleFileSelected(this, 'zip')"
                        />
                        <div id="zip-filename-preview" class="hidden mt-3 text-xs font-bold text-emerald-700 flex items-center justify-center gap-2 bg-white/80 py-2 px-4 rounded-xl border border-emerald-200 w-fit mx-auto">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span id="zip-filename-text"></span>
                        </div>
                    </div>
                </div>

                <!-- Mode 3: Raw Code Snippet Input -->
                <div id="section-code-mode" class="hidden space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5" for="html_code">
                            HTML Markup <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="html_code"
                            name="html_code"
                            rows="6"
                            placeholder="<div class='hero'><h1>Promo Title</h1><p>Description</p><img src='...' /><button>Shop Now</button></div>"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs font-mono bg-slate-900 text-emerald-400 border border-slate-800 focus:ring-2 focus:ring-emerald-500/20 outline-hidden"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5" for="css_code">
                                CSS Stylesheet (Optional)
                            </label>
                            <textarea
                                id="css_code"
                                name="css_code"
                                rows="5"
                                placeholder=".hero { display: flex; background: #111; color: #fff; }"
                                class="w-full px-3.5 py-2.5 rounded-xl text-xs font-mono bg-slate-900 text-sky-400 border border-slate-800 focus:ring-2 focus:ring-sky-500/20 outline-hidden"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5" for="js_code">
                                JavaScript / Animations (Optional)
                            </label>
                            <textarea
                                id="js_code"
                                name="js_code"
                                rows="5"
                                placeholder="console.log('Banner loaded');"
                                class="w-full px-3.5 py-2.5 rounded-xl text-xs font-mono bg-slate-900 text-amber-400 border border-slate-800 focus:ring-2 focus:ring-amber-500/20 outline-hidden"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a
                    href="{{ route('admin.banners.index') }}"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    id="submit-btn"
                    class="px-6 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-sm shadow-emerald-900/20 hover:bg-emerald-700 transition-all flex items-center gap-2 cursor-pointer"
                >
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span id="submit-btn-text">Analyze & Generate Dynamic Banner</span>
                </button>
            </div>
        </form>

        <!-- Live Upload & Analysis Progress Modal -->
        <div id="import-progress-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 animate-in fade-in duration-200">
            <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl border border-slate-100 text-center space-y-6">
                <div class="relative w-20 h-20 mx-auto">
                    <div class="absolute inset-0 rounded-full border-4 border-sky-100 animate-ping opacity-30"></div>
                    <div class="w-20 h-20 rounded-2xl bg-linear-to-tr from-sky-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/30">
                        <i data-lucide="layers" class="w-10 h-10 animate-bounce"></i>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <h3 id="progress-title" class="text-lg font-extrabold text-slate-900">Uploading Photoshop Project...</h3>
                    <p id="progress-subtitle" class="text-xs text-slate-500 font-medium">Please keep this window open while we process your design.</p>
                </div>

                <!-- Progress Bar -->
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-bold text-slate-600">
                        <span id="progress-status-text">Uploading (0%)...</span>
                        <span id="progress-percentage-text">0%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden p-0.5 border border-slate-200">
                        <div id="progress-bar-fill" class="bg-linear-to-r from-sky-500 via-indigo-500 to-emerald-500 h-full rounded-full transition-all duration-200 w-0"></div>
                    </div>
                </div>

                <!-- Processing Checklist Steps -->
                <div class="bg-slate-50 rounded-2xl p-4 text-left text-xs space-y-2 border border-slate-100">
                    <div id="step-1" class="flex items-center gap-2 text-sky-700 font-bold">
                        <div class="w-4 h-4 rounded-full bg-sky-100 flex items-center justify-center text-[10px]">1</div>
                        <span>Transferring binary design file</span>
                    </div>
                    <div id="step-2" class="flex items-center gap-2 text-slate-400 font-medium">
                        <div class="w-4 h-4 rounded-full bg-slate-200 flex items-center justify-center text-[10px]">2</div>
                        <span>Decomposing layers & rasterizing assets</span>
                    </div>
                    <div id="step-3" class="flex items-center gap-2 text-slate-400 font-medium">
                        <div class="w-4 h-4 rounded-full bg-slate-200 flex items-center justify-center text-[10px]">3</div>
                        <span>Synthesizing dynamic fields & launching editor</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function switchImportMode(mode) {
            var psdSection = document.getElementById('section-psd-mode');
            var zipSection = document.getElementById('section-zip-mode');
            var codeSection = document.getElementById('section-code-mode');

            var psdBtn = document.getElementById('tab-psd-btn');
            var zipBtn = document.getElementById('tab-zip-btn');
            var codeBtn = document.getElementById('tab-code-btn');
            var inputType = document.getElementById('import_type_input');

            inputType.value = mode;

            psdSection.classList.add('hidden');
            zipSection.classList.add('hidden');
            codeSection.classList.add('hidden');

            psdBtn.className = 'px-3.5 py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-800 cursor-pointer flex items-center gap-1.5';
            zipBtn.className = 'px-3.5 py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-800 cursor-pointer flex items-center gap-1.5';
            codeBtn.className = 'px-3.5 py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-800 cursor-pointer flex items-center gap-1.5';

            if (mode === 'psd') {
                psdSection.classList.remove('hidden');
                psdBtn.className = 'px-3.5 py-1.5 rounded-lg transition-all bg-white text-sky-700 shadow-xs cursor-pointer flex items-center gap-1.5';
            } else if (mode === 'zip') {
                zipSection.classList.remove('hidden');
                zipBtn.className = 'px-3.5 py-1.5 rounded-lg transition-all bg-white text-emerald-700 shadow-xs cursor-pointer flex items-center gap-1.5';
            } else if (mode === 'raw_code') {
                codeSection.classList.remove('hidden');
                codeBtn.className = 'px-3.5 py-1.5 rounded-lg transition-all bg-white text-emerald-700 shadow-xs cursor-pointer flex items-center gap-1.5';
            }
        }

        function handleFileSelected(input, type) {
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var previewId = type === 'psd' ? 'psd-filename-preview' : 'zip-filename-preview';
                var textId = type === 'psd' ? 'psd-filename-text' : 'zip-filename-text';
                var preview = document.getElementById(previewId);
                var text = document.getElementById(textId);
                
                var sizeMb = (file.size / 1048576).toFixed(2);
                var sizeText = file.size > 1048576 ? sizeMb + ' MB' : (file.size / 1024).toFixed(0) + ' KB';

                preview.classList.remove('hidden');
                text.textContent = file.name + ' (' + sizeText + ')';
            }
        }

        // Handle AJAX upload with live progress bar
        document.getElementById('banner-import-form').addEventListener('submit', function(e) {
            e.preventDefault();

            var form = this;
            var formData = new FormData(form);
            var mode = document.getElementById('import_type_input').value;

            // Basic validation
            if (mode === 'psd') {
                var psdInput = document.getElementById('psd_file');
                if (!psdInput.files || !psdInput.files[0]) {
                    alert('Please select a Photoshop (.PSD) file to upload.');
                    return;
                }
            } else if (mode === 'zip') {
                var zipInput = document.getElementById('zip_file');
                if (!zipInput.files || !zipInput.files[0]) {
                    alert('Please select a ZIP file to upload.');
                    return;
                }
            }

            var modal = document.getElementById('import-progress-modal');
            var progressFill = document.getElementById('progress-bar-fill');
            var progressPercent = document.getElementById('progress-percentage-text');
            var progressStatus = document.getElementById('progress-status-text');
            var progressTitle = document.getElementById('progress-title');
            var step1 = document.getElementById('step-1');
            var step2 = document.getElementById('step-2');
            var step3 = document.getElementById('step-3');

            modal.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();

            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            // Track upload progress
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    var percent = Math.round((e.loaded / e.total) * 100);
                    var loadedMb = (e.loaded / 1048576).toFixed(1);
                    var totalMb = (e.total / 1048576).toFixed(1);

                    progressFill.style.width = percent + '%';
                    progressPercent.textContent = percent + '%';
                    progressStatus.textContent = 'Uploading ' + loadedMb + ' MB / ' + totalMb + ' MB';

                    if (percent >= 100) {
                        progressTitle.textContent = 'Analyzing Photoshop Layers...';
                        progressStatus.textContent = 'Decomposing 30+ layers into assets...';
                        step1.className = 'flex items-center gap-2 text-emerald-600 font-bold';
                        step1.innerHTML = '<i data-lucide="check" class="w-4 h-4 text-emerald-600"></i><span>Design file uploaded successfully</span>';
                        step2.className = 'flex items-center gap-2 text-sky-700 font-bold';
                        if (window.lucide) lucide.createIcons();
                    }
                }
            };

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    step2.className = 'flex items-center gap-2 text-emerald-600 font-bold';
                    step2.innerHTML = '<i data-lucide="check" class="w-4 h-4 text-emerald-600"></i><span>Layers decomposed & assets extracted</span>';
                    step3.className = 'flex items-center gap-2 text-emerald-600 font-bold';
                    step3.innerHTML = '<i data-lucide="check" class="w-4 h-4 text-emerald-600"></i><span>Opening Visual Editor...</span>';
                    if (window.lucide) lucide.createIcons();

                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.redirect_url) {
                            window.location.href = res.redirect_url;
                            return;
                        }
                    } catch(err) {}

                    window.location.href = '{{ route("admin.banners.index") }}';
                } else {
                    modal.classList.add('hidden');
                    var errorMsg = 'Import failed (Status: ' + xhr.status + ').';
                    try {
                        var errRes = JSON.parse(xhr.responseText);
                        if (errRes.message) errorMsg = errRes.message;
                    } catch(e) {}
                    alert(errorMsg);
                }
            };

            xhr.onerror = function() {
                modal.classList.add('hidden');
                alert('Connection error occurred while uploading. If you recently updated php.ini, please restart "php artisan serve".');
            };

            xhr.send(formData);
        });
    </script>
@endpush
