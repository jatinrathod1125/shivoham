<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});

/*
|--------------------------------------------------------------------------
| Admin Guest Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    });

    /*
    |--------------------------------------------------------------------------
    | Authenticated Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });

        // Category Management
        Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::post('/categories/{category}/toggle-featured', [CategoryController::class, 'toggleFeatured'])->name('categories.toggle-featured');
        Route::resource('categories', CategoryController::class)->names('categories');

        // Brand Management
        Route::post('/brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggle-status');
        Route::post('/brands/{brand}/toggle-featured', [BrandController::class, 'toggleFeatured'])->name('brands.toggle-featured');
        Route::resource('brands', BrandController::class)->names('brands');

        // Product Management
        Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::post('/products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
        Route::post('/products/{product}/quick-stock', [ProductController::class, 'quickStockUpdate'])->name('products.quick-stock');
        Route::resource('products', ProductController::class)->names('products');

        // Inventory Management
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
        Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

        // Customer Management
        Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
        Route::resource('customers', CustomerController::class)->names('customers');

        // Order Management
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::post('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/orders/{order}/update-payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');
        Route::resource('orders', OrderController::class)->names('orders');

        // Offers & Deals Management
        Route::post('/offers/{offer}/toggle-status', [OfferController::class, 'toggleStatus'])->name('offers.toggle-status');
        Route::resource('offers', OfferController::class)->names('offers');

        // Coupon Codes Management
        Route::post('/coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
        Route::resource('coupons', CouponController::class)->names('coupons');

        // Promotional Banners & Sliders Management
        Route::post('/banners/{banner}/toggle-status', [BannerController::class, 'toggleStatus'])->name('banners.toggle-status');
        Route::resource('banners', BannerController::class)->names('banners');

        // Analytics & Business Reports
        Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('/reports/inventory/export', [ReportController::class, 'exportInventoryCsv'])->name('reports.inventory.export');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // System Settings
        Route::get('/settings/localization', [SettingController::class, 'localization'])->name('settings.localization');
        Route::put('/settings/localization', [SettingController::class, 'updateLocalization'])->name('settings.update-localization');
        Route::get('/settings/tax', [SettingController::class, 'tax'])->name('settings.tax');
        Route::put('/settings/tax', [SettingController::class, 'updateTax'])->name('settings.update-tax');
        Route::get('/settings/hours', [SettingController::class, 'hours'])->name('settings.hours');
        Route::put('/settings/hours', [SettingController::class, 'updateHours'])->name('settings.update-hours');
        Route::get('/settings/shipping', [SettingController::class, 'shipping'])->name('settings.shipping');
        Route::put('/settings/shipping', [SettingController::class, 'updateShipping'])->name('settings.update-shipping');
        Route::get('/settings/payments', [SettingController::class, 'payments'])->name('settings.payments');
        Route::put('/settings/payments', [SettingController::class, 'updatePayments'])->name('settings.update-payments');
        Route::get('/settings/inventory', [SettingController::class, 'inventorySettings'])->name('settings.inventory');
        Route::put('/settings/inventory', [SettingController::class, 'updateInventorySettings'])->name('settings.update-inventory');
        Route::put('/settings/general', [SettingController::class, 'updateGeneral'])->name('settings.update-general');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');

        // Design System / Component preview
        Route::get('/design-system', function () {
            return view('admin.design-system', [
                'title' => 'Design System - ' . config('admin.name', 'Grocery Admin'),
            ]);
        })->name('design-system');
    });
});
