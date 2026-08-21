@if(session('success'))
    <div class="mb-5">
        <x-admin.alert type="success" :title="session('success_title')">
            {{ session('success') }}
        </x-admin.alert>
    </div>
@endif

@if(session('error'))
    <div class="mb-5">
        <x-admin.alert type="error" :title="session('error_title', 'Error Occurred')">
            {{ session('error') }}
        </x-admin.alert>
    </div>
@endif

@if(session('warning'))
    <div class="mb-5">
        <x-admin.alert type="warning" :title="session('warning_title')">
            {{ session('warning') }}
        </x-admin.alert>
    </div>
@endif

@if(session('info'))
    <div class="mb-5">
        <x-admin.alert type="info" :title="session('info_title')">
            {{ session('info') }}
        </x-admin.alert>
    </div>
@endif

@if($errors->any() && !session('hide_validation_alert'))
    <div class="mb-5">
        <x-admin.alert type="error" title="Please check the form for errors">
            <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-admin.alert>
    </div>
@endif
