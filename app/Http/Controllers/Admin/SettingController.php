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
     * Display Currency & Localization Settings.
     */
    public function localization(): View
    {
        return view('admin.settings.localization', [
            'title' => 'Localization & Currency - ' . config('admin.name', 'Grocery Admin'),
            'settings' => [
                'currency_code' => Setting::get('currency_code', 'USD'),
                'currency_symbol' => Setting::get('currency_symbol', '$'),
                'currency_position' => Setting::get('currency_position', 'left'),
                'timezone' => Setting::get('timezone', 'America/Chicago'),
                'date_format' => Setting::get('date_format', 'M d, Y'),
                'time_format' => Setting::get('time_format', '12h'),
                'decimal_separator' => Setting::get('decimal_separator', '.'),
                'thousands_separator' => Setting::get('thousands_separator', ','),
            ],
        ]);
    }

    /**
     * Update Currency & Localization Settings.
     */
    public function updateLocalization(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency_code' => ['required', 'string', 'max:5'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'currency_position' => ['required', 'in:left,right'],
            'timezone' => ['required', 'string', 'max:50'],
            'date_format' => ['required', 'string', 'max:20'],
            'time_format' => ['required', 'in:12h,24h'],
            'decimal_separator' => ['required', 'string', 'max:1'],
            'thousands_separator' => ['nullable', 'string', 'max:1'],
        ]);

        foreach ($validated as $key => $val) {
            Setting::set($key, $val ?? '', 'localization', 'string');
        }

        return redirect()->route('admin.settings.localization')
            ->with('toast_success', 'Localization & Currency settings updated.');
    }

    /**
     * Display Tax & Financial Pricing Settings.
     */
    public function tax(): View
    {
        return view('admin.settings.tax', [
            'title' => 'Tax & Pricing - ' . config('admin.name', 'Grocery Admin'),
            'settings' => [
                'enable_tax' => Setting::get('enable_tax', true),
                'tax_rate_percentage' => Setting::get('tax_rate_percentage', '8.00'),
                'tax_calculation_type' => Setting::get('tax_calculation_type', 'exclusive'),
                'tax_number' => Setting::get('tax_number', 'TAX-US-982341-GH'),
                'tax_label' => Setting::get('tax_label', 'Sales Tax (GST/VAT)'),
            ],
        ]);
    }

    /**
     * Update Tax & Financial Pricing Settings.
     */
    public function updateTax(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enable_tax' => ['nullable', 'boolean'],
            'tax_rate_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_calculation_type' => ['required', 'in:inclusive,exclusive'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'tax_label' => ['required', 'string', 'max:50'],
        ]);

        Setting::set('enable_tax', $request->boolean('enable_tax'), 'orders', 'boolean');
        Setting::set('tax_rate_percentage', $validated['tax_rate_percentage'], 'orders', 'decimal');
        Setting::set('tax_calculation_type', $validated['tax_calculation_type'], 'orders', 'string');
        Setting::set('tax_number', $validated['tax_number'] ?? '', 'orders', 'string');
        Setting::set('tax_label', $validated['tax_label'], 'orders', 'string');

        return redirect()->route('admin.settings.tax')
            ->with('toast_success', 'Tax & pricing configurations saved.');
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
     * Display Shipping & Delivery Slots Settings.
     */
    public function shipping(): View
    {
        $defaultSlots = ['08:00 - 11:00', '11:00 - 14:00', '14:00 - 17:00', '17:00 - 20:00'];

        return view('admin.settings.shipping', [
            'title' => 'Shipping & Delivery Slots - ' . config('admin.name', 'Grocery Admin'),
            'settings' => [
                'free_shipping_threshold' => Setting::get('free_shipping_threshold', '40.00'),
                'default_shipping_fee' => Setting::get('default_shipping_fee', '4.99'),
                'express_shipping_fee' => Setting::get('express_shipping_fee', '9.99'),
                'delivery_slots' => Setting::get('delivery_slots', $defaultSlots),
                'max_orders_per_slot' => Setting::get('max_orders_per_slot', 25),
                'slot_cutoff_minutes' => Setting::get('slot_cutoff_minutes', 60),
            ],
        ]);
    }

    /**
     * Update Shipping & Delivery Slots Settings.
     */
    public function updateShipping(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'free_shipping_threshold' => ['required', 'numeric', 'min:0'],
            'default_shipping_fee' => ['required', 'numeric', 'min:0'],
            'express_shipping_fee' => ['required', 'numeric', 'min:0'],
            'delivery_slots' => ['required', 'array', 'min:1'],
            'delivery_slots.*' => ['required', 'string', 'max:50'],
            'max_orders_per_slot' => ['required', 'integer', 'min:1', 'max:500'],
            'slot_cutoff_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
        ]);

        Setting::set('free_shipping_threshold', $validated['free_shipping_threshold'], 'orders', 'decimal');
        Setting::set('default_shipping_fee', $validated['default_shipping_fee'], 'orders', 'decimal');
        Setting::set('express_shipping_fee', $validated['express_shipping_fee'], 'orders', 'decimal');
        Setting::set('delivery_slots', array_values(array_filter($validated['delivery_slots'])), 'operations', 'json');
        Setting::set('max_orders_per_slot', $validated['max_orders_per_slot'], 'operations', 'integer');
        Setting::set('slot_cutoff_minutes', $validated['slot_cutoff_minutes'], 'operations', 'integer');

        return redirect()->route('admin.settings.shipping')
            ->with('toast_success', 'Shipping fees & delivery slots configuration saved.');
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
                // PayPal
                'paypal_enabled' => Setting::get('paypal_enabled', false),
                'paypal_mode' => Setting::get('paypal_mode', 'sandbox'),
                'paypal_client_id' => Setting::get('paypal_client_id', ''),
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
            'paypal_enabled' => ['nullable', 'boolean'],
            'paypal_mode' => ['required', 'in:sandbox,live'],
            'paypal_client_id' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::set('cod_enabled', $request->boolean('cod_enabled'), 'payments', 'boolean');
        Setting::set('cod_min_amount', $validated['cod_min_amount'] ?? '0.00', 'payments', 'decimal');
        Setting::set('cod_max_amount', $validated['cod_max_amount'] ?? '500.00', 'payments', 'decimal');

        Setting::set('stripe_enabled', $request->boolean('stripe_enabled'), 'payments', 'boolean');
        Setting::set('stripe_mode', $validated['stripe_mode'], 'payments', 'string');
        Setting::set('stripe_publishable_key', $validated['stripe_publishable_key'] ?? '', 'payments', 'string');
        Setting::set('stripe_secret_key', $validated['stripe_secret_key'] ?? '', 'payments', 'string');

        Setting::set('paypal_enabled', $request->boolean('paypal_enabled'), 'payments', 'boolean');
        Setting::set('paypal_mode', $validated['paypal_mode'], 'payments', 'string');
        Setting::set('paypal_client_id', $validated['paypal_client_id'] ?? '', 'payments', 'string');

        return redirect()->route('admin.settings.payments')
            ->with('toast_success', 'Payment gateway configurations saved.');
    }

    /**
     * Display Stock & Inventory Alert Settings.
     */
    public function inventorySettings(): View
    {
        return view('admin.settings.inventory', [
            'title' => 'Stock & Inventory Alerts - ' . config('admin.name', 'Grocery Admin'),
            'settings' => [
                'default_low_stock_threshold' => Setting::get('default_low_stock_threshold', 10),
                'hide_out_of_stock' => Setting::get('hide_out_of_stock', false),
                'allow_backorders' => Setting::get('allow_backorders', false),
                'enable_stock_alert_emails' => Setting::get('enable_stock_alert_emails', true),
                'stock_alert_email' => Setting::get('stock_alert_email', 'inventory@grocery.local'),
            ],
        ]);
    }

    /**
     * Update Stock & Inventory Alert Settings.
     */
    public function updateInventorySettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_low_stock_threshold' => ['required', 'integer', 'min:1', 'max:1000'],
            'hide_out_of_stock' => ['nullable', 'boolean'],
            'allow_backorders' => ['nullable', 'boolean'],
            'enable_stock_alert_emails' => ['nullable', 'boolean'],
            'stock_alert_email' => ['nullable', 'email', 'max:150'],
        ]);

        Setting::set('default_low_stock_threshold', $validated['default_low_stock_threshold'], 'inventory', 'integer');
        Setting::set('hide_out_of_stock', $request->boolean('hide_out_of_stock'), 'inventory', 'boolean');
        Setting::set('allow_backorders', $request->boolean('allow_backorders'), 'inventory', 'boolean');
        Setting::set('enable_stock_alert_emails', $request->boolean('enable_stock_alert_emails'), 'inventory', 'boolean');
        Setting::set('stock_alert_email', $validated['stock_alert_email'] ?? '', 'inventory', 'string');

        return redirect()->route('admin.settings.inventory')
            ->with('toast_success', 'Stock & inventory alert settings updated.');
    }
}
