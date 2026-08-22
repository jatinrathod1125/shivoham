@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="System Settings"
        subtitle="Manage store identity, currency, taxation rules, delivery zones, and payment gateways."
        :breadcrumbs="[
            ['title' => 'Settings', 'url' => ''],
            ['title' => 'Store Profile', 'url' => '']
        ]"
    />

    <!-- Settings Navigation Tabs -->
    <x-admin.settings-nav active="general" />

    <!-- Store Profile Form -->
    <form method="POST" action="{{ route('admin.settings.update-general') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Identity & Contact -->
            <div class="lg:col-span-8 space-y-6">
                <x-admin.card title="Store Identity & Contact Information" subtitle="Public store details shown on invoices, emails, and footer" icon="store">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form.input
                                name="store_name"
                                label="Store Name"
                                placeholder="Fresh Groceries Hub"
                                :required="true"
                                :value="old('store_name', $settings['store_name'])"
                            />

                            <x-form.input
                                name="store_tagline"
                                label="Tagline / Motto"
                                placeholder="Your Everyday Organic Grocery Partner"
                                :value="old('store_tagline', $settings['store_tagline'])"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form.input
                                type="email"
                                name="store_email"
                                label="Customer Support Email"
                                placeholder="support@grocery.local"
                                :required="true"
                                :value="old('store_email', $settings['store_email'])"
                            />

                            <x-form.input
                                name="store_phone"
                                label="Store Hotline / Phone"
                                placeholder="+1 (800) 555-GROCERY"
                                :value="old('store_phone', $settings['store_phone'])"
                            />
                        </div>

                        <x-form.input
                            name="store_address"
                            label="Physical Warehouse / Store Address"
                            placeholder="100 Market Square, Suite 400, Chicago, IL 60601"
                            :value="old('store_address', $settings['store_address'])"
                        />

                        <x-form.input
                            name="support_hours"
                            label="Customer Support Operational Window"
                            placeholder="Mon - Sat: 8:00 AM - 9:00 PM"
                            :value="old('support_hours', $settings['support_hours'])"
                        />
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Logo & Action -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Store Logo -->
                <x-admin.card title="Store Logo" subtitle="Brand branding mark" icon="image">
                    <div class="space-y-3">
                        <div
                            id="logo-dropzone"
                            class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-emerald-500 hover:bg-emerald-50/20 transition-all cursor-pointer group"
                        >
                            <input
                                type="file"
                                id="logo-file-input"
                                name="store_logo"
                                accept="image/*"
                                class="hidden"
                            />
                            @if($settings['store_logo'])
                                <div id="logo-preview-box">
                                    <img id="logo-preview-img" src="{{ $settings['store_logo'] }}" alt="Store Logo" class="h-20 max-w-full object-contain mx-auto rounded-lg" />
                                    <p class="text-[10px] text-emerald-600 font-semibold mt-2">Click to replace logo</p>
                                </div>
                            @else
                                <div id="logo-placeholder" class="space-y-1.5 py-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-emerald-100 text-slate-500 group-hover:text-emerald-600 flex items-center justify-center mx-auto transition-colors">
                                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-700">Upload Store Logo</p>
                                    <p class="text-[10px] text-slate-400">PNG, SVG up to 2MB</p>
                                </div>
                                <div id="logo-preview-box" class="hidden">
                                    <img id="logo-preview-img" src="" alt="Store Logo Preview" class="h-20 max-w-full object-contain mx-auto rounded-lg" />
                                    <p class="text-[10px] text-emerald-600 font-semibold mt-2">Click to replace</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-admin.card>

                <!-- Save Action Card -->
                <x-admin.card title="Save Changes" icon="check-circle-2">
                    <p class="text-xs text-slate-500 mb-4">Updates will take effect immediately across admin invoices and email footers.</p>

                    <x-admin.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="check"
                        class="w-full"
                    >
                        Save Store Profile
                    </x-admin.button>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#logo-dropzone').on('click', function (e) {
            if (e.target !== document.getElementById('logo-file-input')) {
                $('#logo-file-input').trigger('click');
            }
        });

        $('#logo-file-input').on('click', function (e) {
            e.stopPropagation();
        });

        $('#logo-file-input').on('change', function () {
            const input = this;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#logo-preview-img').attr('src', e.target.result);
                    $('#logo-placeholder').addClass('hidden');
                    $('#logo-preview-box').removeClass('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
    });
</script>
@endpush
