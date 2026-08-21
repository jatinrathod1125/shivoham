<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display Store Profile & General Settings.
     */
    public function index(): View
    {
        return view('admin.settings.general', [
            'title' => 'General Settings - ' . config('admin.name', 'Grocery Admin'),
            'settings' => [
                'store_name' => Setting::get('store_name', 'Fresh Groceries Hub'),
                'store_tagline' => Setting::get('store_tagline', 'Your Everyday Organic Grocery Partner'),
                'store_email' => Setting::get('store_email', 'support@grocery.local'),
                'store_phone' => Setting::get('store_phone', '+1 (800) 555-GROCERY'),
                'store_address' => Setting::get('store_address', '100 Market Square, Suite 400, Chicago, IL 60601'),
                'store_logo' => Setting::get('store_logo', null),
                'support_hours' => Setting::get('support_hours', 'Mon - Sat: 8:00 AM - 9:00 PM'),
            ],
        ]);
    }

    /**
     * Update Store Profile & General Settings.
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:100'],
            'store_tagline' => ['nullable', 'string', 'max:255'],
            'store_email' => ['required', 'email', 'max:150'],
            'store_phone' => ['nullable', 'string', 'max:50'],
            'store_address' => ['nullable', 'string', 'max:255'],
            'support_hours' => ['nullable', 'string', 'max:100'],
            'store_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        Setting::set('store_name', $validated['store_name'], 'general', 'string');
        Setting::set('store_tagline', $validated['store_tagline'] ?? '', 'general', 'string');
        Setting::set('store_email', $validated['store_email'], 'general', 'string');
        Setting::set('store_phone', $validated['store_phone'] ?? '', 'general', 'string');
        Setting::set('store_address', $validated['store_address'] ?? '', 'general', 'string');
        Setting::set('support_hours', $validated['support_hours'] ?? '', 'general', 'string');

        if ($request->hasFile('store_logo')) {
            $path = $request->file('store_logo')->store('settings', 'public');
            Setting::set('store_logo', Storage::url($path), 'general', 'string');
        }

        return redirect()->route('admin.settings.index')
            ->with('toast_success', 'Store profile settings saved successfully.');
    }

    /**
     * Display Currency Configuration Settings.
     */
    public function localization(): View
    {
        return view('admin.settings.localization', [
            'title' => 'Currency Configuration - ' . config('admin.name', 'Grocery Admin'),
            'settings' => [
                'currency_code' => Setting::get('currency_code', 'USD'),
                'currency_symbol' => Setting::get('currency_symbol', '$'),
                'currency_position' => Setting::get('currency_position', 'left'),
                'decimal_separator' => Setting::get('decimal_separator', '.'),
                'thousands_separator' => Setting::get('thousands_separator', ','),
            ],
        ]);
    }

    /**
     * Update Currency Configuration Settings.
     */
    public function updateLocalization(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency_code' => ['required', 'string', 'max:5'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'currency_position' => ['required', 'in:left,right'],
            'decimal_separator' => ['required', 'string', 'max:1'],
            'thousands_separator' => ['nullable', 'string', 'max:1'],
        ]);

        foreach ($validated as $key => $val) {
            Setting::set($key, $val ?? '', 'localization', 'string');
        }

        return redirect()->route('admin.settings.localization')
            ->with('toast_success', 'Currency configuration settings updated.');
    }

    /**
     * Display Store Operating Hours & Service Availability.
     */
    public function hours(): View
    {
        $defaultSchedule = [
            'monday' => ['open' => '07:00', 'close' => '21:00', 'is_closed' => false],
            'tuesday' => ['open' => '07:00', 'close' => '21:00', 'is_closed' => false],
            'wednesday' => ['open' => '07:00', 'close' => '21:00', 'is_closed' => false],
            'thursday' => ['open' => '07:00', 'close' => '21:00', 'is_closed' => false],
            'friday' => ['open' => '07:00', 'close' => '22:00', 'is_closed' => false],
            'saturday' => ['open' => '08:00', 'close' => '22:00', 'is_closed' => false],
            'sunday' => ['open' => '08:00', 'close' => '20:00', 'is_closed' => false],
        ];

        return view('admin.settings.hours', [
            'title' => 'Operating Hours - ' . config('admin.name', 'Grocery Admin'),
            'settings' => [
                'store_status' => Setting::get('store_status', 'online'),
                'schedule' => Setting::get('operating_schedule', $defaultSchedule),
                'notice_message' => Setting::get('store_notice_message', 'Fresh express grocery delivery active 7 days a week!'),
            ],
        ]);
    }

    /**
     * Update Store Operating Hours & Service Availability.
     */
    public function updateHours(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_status' => ['required', 'in:online,maintenance,closed'],
            'notice_message' => ['nullable', 'string', 'max:255'],
            'schedule' => ['required', 'array'],
            'schedule.*.open' => ['nullable', 'string'],
            'schedule.*.close' => ['nullable', 'string'],
            'schedule.*.is_closed' => ['nullable'],
        ]);

        $sanitizedSchedule = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            $dayData = $validated['schedule'][$day] ?? [];
            $sanitizedSchedule[$day] = [
                'open' => $dayData['open'] ?? '08:00',
                'close' => $dayData['close'] ?? '21:00',
                'is_closed' => !empty($dayData['is_closed']),
            ];
        }

        Setting::set('store_status', $validated['store_status'], 'operations', 'string');
        Setting::set('store_notice_message', $validated['notice_message'] ?? '', 'operations', 'string');
        Setting::set('operating_schedule', $sanitizedSchedule, 'operations', 'json');

        return redirect()->route('admin.settings.hours')
            ->with('toast_success', 'Store operating hours and availability updated.');
    }

    /**
     * Display Payment Gateways Settings.
     */
    public function payments(): View
    {
        return view('admin.settings.payments', [
            'title' => 'Payment Gateways - ' . config('admin.name', 'Grocery Admin'),
            'settings' => [
                // Cash on Delivery
                'cod_enabled' => Setting::get('cod_enabled', true),
                'cod_min_amount' => Setting::get('cod_min_amount', '0.00'),
                'cod_max_amount' => Setting::get('cod_max_amount', '300.00'),
                // Stripe
                'stripe_enabled' => Setting::get('stripe_enabled', true),
                'stripe_mode' => Setting::get('stripe_mode', 'test'),
                'stripe_publishable_key' => Setting::get('stripe_publishable_key', 'pk_test_sample_51O8G'),
                'stripe_secret_key' => Setting::get('stripe_secret_key', 'sk_test_sample_51O8G'),
            ],
        ]);
    }

    /**
     * Update Payment Gateways Settings.
     */
    public function updatePayments(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cod_enabled' => ['nullable', 'boolean'],
            'cod_min_amount' => ['nullable', 'numeric', 'min:0'],
            'cod_max_amount' => ['nullable', 'numeric', 'min:0'],
            'stripe_enabled' => ['nullable', 'boolean'],
            'stripe_mode' => ['required', 'in:test,live'],
            'stripe_publishable_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::set('cod_enabled', $request->boolean('cod_enabled'), 'payments', 'boolean');
        Setting::set('cod_min_amount', $validated['cod_min_amount'] ?? '0.00', 'payments', 'decimal');
        Setting::set('cod_max_amount', $validated['cod_max_amount'] ?? '500.00', 'payments', 'decimal');

        Setting::set('stripe_enabled', $request->boolean('stripe_enabled'), 'payments', 'boolean');
        Setting::set('stripe_mode', $validated['stripe_mode'], 'payments', 'string');
        Setting::set('stripe_publishable_key', $validated['stripe_publishable_key'] ?? '', 'payments', 'string');
        Setting::set('stripe_secret_key', $validated['stripe_secret_key'] ?? '', 'payments', 'string');

        return redirect()->route('admin.settings.payments')
            ->with('toast_success', 'Payment gateway configurations saved.');
    }

    /**
     * Display Stock & Inventory Settings.
     */
    public function inventorySettings(): View
    {
        return view('admin.settings.inventory', [
            'title' => 'Stock & Inventory Settings - ' . config('admin.name', 'Grocery Admin'),
            'settings' => [
                'default_low_stock_threshold' => Setting::get('default_low_stock_threshold', 10),
                'hide_out_of_stock' => Setting::get('hide_out_of_stock', false),
                'allow_backorders' => Setting::get('allow_backorders', false),
            ],
        ]);
    }

    /**
     * Update Stock & Inventory Settings.
     */
    public function updateInventorySettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_low_stock_threshold' => ['required', 'integer', 'min:1', 'max:1000'],
            'hide_out_of_stock' => ['nullable', 'boolean'],
            'allow_backorders' => ['nullable', 'boolean'],
        ]);

        Setting::set('default_low_stock_threshold', $validated['default_low_stock_threshold'], 'inventory', 'integer');
        Setting::set('hide_out_of_stock', $request->boolean('hide_out_of_stock'), 'inventory', 'boolean');
        Setting::set('allow_backorders', $request->boolean('allow_backorders'), 'inventory', 'boolean');

        return redirect()->route('admin.settings.inventory')
            ->with('toast_success', 'Stock & inventory settings updated.');
    }
}
