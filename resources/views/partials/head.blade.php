<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title ?? config('admin.name', 'Grocery Admin') }} - {{ config('admin.store_name', 'Fresh Hub') }}</title>

@if(config('admin.favicon'))
    <link rel="icon" type="image/x-icon" href="{{ config('admin.favicon') }}">
@endif

<!-- Preconnect & Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

<!-- Dynamic Brand Theme Variables -->
<style>
    :root {
        --color-primary: {{ config('admin.colors.primary', '#16a34a') }};
        --color-primary-hover: {{ config('admin.colors.primary_hover', '#15803d') }};
        --color-sidebar: {{ config('admin.colors.dark_sidebar', '#0f172a') }};
    }
</style>

<!-- jQuery (Synchronous load for inline script compatibility) -->
<script src="{{ asset('js/jquery.min.js') }}"></script>

<!-- Vite Styles & Scripts -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
