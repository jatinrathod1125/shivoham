<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Application Branding Configuration
    |--------------------------------------------------------------------------
    |
    | Centralized settings for admin panel branding, colors, logos, and UI tokens.
    | Changing values here updates the entire admin theme consistently.
    |
    */

    'name' => env('ADMIN_APP_NAME', 'Grocery Admin'),
    'store_name' => env('ADMIN_STORE_NAME', 'Fresh Groceries Hub'),
    'tagline' => env('ADMIN_APP_TAGLINE', 'Supermarket & Grocery Management Panel'),

    'logo' => [
        'full' => env('ADMIN_LOGO_FULL', null),
        'icon' => env('ADMIN_LOGO_ICON', null),
        'dark_sidebar' => env('ADMIN_LOGO_DARK', null),
    ],

    'favicon' => env('ADMIN_FAVICON', null),

    'colors' => [
        'primary' => env('ADMIN_COLOR_PRIMARY', '#16a34a'),
        'primary_hover' => env('ADMIN_COLOR_PRIMARY_HOVER', '#15803d'),
        'secondary' => env('ADMIN_COLOR_SECONDARY', '#475569'),
        'success' => env('ADMIN_COLOR_SUCCESS', '#22c55e'),
        'warning' => env('ADMIN_COLOR_WARNING', '#f59e0b'),
        'danger' => env('ADMIN_COLOR_DANGER', '#ef4444'),
        'info' => env('ADMIN_COLOR_INFO', '#3b82f6'),
        'dark_sidebar' => env('ADMIN_COLOR_SIDEBAR', '#0f172a'),
    ],

    'pagination' => [
        'per_page' => 15,
    ],

    'contact' => [
        'email' => env('ADMIN_CONTACT_EMAIL', 'admin@grocerysystem.local'),
        'phone' => env('ADMIN_CONTACT_PHONE', '+1 (555) 019-2834'),
        'address' => env('ADMIN_STORE_ADDRESS', '100 Market Street, Suite 400'),
    ],
];
