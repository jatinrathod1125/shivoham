<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * Test guest cannot access settings.
     */
    public function test_guest_cannot_access_settings(): void
    {
        $response = $this->get('/admin/settings');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view and update store profile settings.
     */
    public function test_admin_can_update_general_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');
        $response->assertStatus(200);
        $response->assertSee('System Settings');
        $response->assertSee('Store Identity');

        $updateResponse = $this->actingAs($this->admin)->put('/admin/settings/general', [
            'store_name' => 'Prime Supermarket',
            'store_tagline' => 'Best organic produce',
            'store_email' => 'admin@prime.local',
            'store_phone' => '+1 888 999 0000',
            'store_address' => '77 Organic Way, Austin TX',
            'support_hours' => 'Mon-Sun: 7am - 10pm',
        ]);

        $updateResponse->assertRedirect('/admin/settings');
        $updateResponse->assertSessionHas('toast_success');

        $this->assertEquals('Prime Supermarket', Setting::get('store_name'));
        $this->assertEquals('admin@prime.local', Setting::get('store_email'));
    }

    /**
     * Test admin can update currency settings.
     */
    public function test_admin_can_update_localization_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings/localization');
        $response->assertStatus(200);
        $response->assertSee('Currency Configuration');

        $updateResponse = $this->actingAs($this->admin)->put('/admin/settings/localization', [
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'currency_position' => 'right',
            'decimal_separator' => '.',
            'thousands_separator' => ',',
        ]);

        $updateResponse->assertRedirect('/admin/settings/localization');
        $updateResponse->assertSessionHas('toast_success');

        $this->assertEquals('EUR', Setting::get('currency_code'));
        $this->assertEquals('€', Setting::get('currency_symbol'));
    }

    /**
     * Test admin can update operating hours.
     */
    public function test_admin_can_update_operating_hours(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings/hours');
        $response->assertStatus(200);
        $response->assertSee('Weekly Operating Schedule');

        $updateResponse = $this->actingAs($this->admin)->put('/admin/settings/hours', [
            'store_status' => 'online',
            'notice_message' => 'Express delivery available!',
            'schedule' => [
                'monday' => ['open' => '08:00', 'close' => '20:00', 'is_closed' => 0],
                'tuesday' => ['open' => '08:00', 'close' => '20:00', 'is_closed' => 0],
                'wednesday' => ['open' => '08:00', 'close' => '20:00', 'is_closed' => 0],
                'thursday' => ['open' => '08:00', 'close' => '20:00', 'is_closed' => 0],
                'friday' => ['open' => '08:00', 'close' => '22:00', 'is_closed' => 0],
                'saturday' => ['open' => '09:00', 'close' => '22:00', 'is_closed' => 0],
                'sunday' => ['open' => '10:00', 'close' => '18:00', 'is_closed' => 1],
            ],
        ]);

        $updateResponse->assertRedirect('/admin/settings/hours');
        $updateResponse->assertSessionHas('toast_success');

        $this->assertEquals('online', Setting::get('store_status'));
        $schedule = Setting::get('operating_schedule');
        $this->assertTrue($schedule['sunday']['is_closed']);
    }

    /**
     * Test admin can update payment gateways.
     */
    public function test_admin_can_update_payment_gateways(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings/payments');
        $response->assertStatus(200);
        $response->assertSee('Cash on Delivery');
        $response->assertSee('Stripe');

        $updateResponse = $this->actingAs($this->admin)->put('/admin/settings/payments', [
            'cod_enabled' => 1,
            'cod_min_amount' => '10.00',
            'cod_max_amount' => '250.00',
            'stripe_enabled' => 1,
            'stripe_mode' => 'live',
            'stripe_publishable_key' => 'pk_live_12345',
            'stripe_secret_key' => 'sk_live_12345',
        ]);

        $updateResponse->assertRedirect('/admin/settings/payments');
        $updateResponse->assertSessionHas('toast_success');

        $this->assertTrue(Setting::get('cod_enabled'));
        $this->assertEquals('live', Setting::get('stripe_mode'));
        $this->assertEquals('pk_live_12345', Setting::get('stripe_publishable_key'));
    }

    /**
     * Test admin can update stock & inventory settings.
     */
    public function test_admin_can_update_inventory_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings/inventory');
        $response->assertStatus(200);
        $response->assertSee('Inventory Thresholds &amp; Visibility', false);

        $updateResponse = $this->actingAs($this->admin)->put('/admin/settings/inventory', [
            'default_low_stock_threshold' => 15,
            'hide_out_of_stock' => 1,
            'allow_backorders' => 0,
        ]);

        $updateResponse->assertRedirect('/admin/settings/inventory');
        $updateResponse->assertSessionHas('toast_success');

        $this->assertEquals(15, Setting::get('default_low_stock_threshold'));
        $this->assertTrue(Setting::get('hide_out_of_stock'));
        $this->assertFalse(Setting::get('allow_backorders'));
    }
}
