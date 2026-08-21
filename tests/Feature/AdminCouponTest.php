<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponTest extends TestCase
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
     * Test guest cannot access coupons index.
     */
    public function test_guest_cannot_access_coupons(): void
    {
        $response = $this->get('/admin/coupons');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view coupons index with KPIs.
     */
    public function test_admin_can_view_coupons_index(): void
    {
        Coupon::factory()->create([
            'code' => 'ORGANIC20',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 20,
            'min_spend' => 40.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/coupons');

        $response->assertStatus(200);
        $response->assertSee('Coupon Codes');
        $response->assertSee('ORGANIC20');
        $response->assertSee('20% OFF');
        $response->assertSee('Total Coupons');
        $response->assertSee('Active Codes');
    }

    /**
     * Test admin can create coupon with auto-uppercasing.
     */
    public function test_admin_can_create_coupon(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/coupons', [
            'code' => 'summerdeal15',
            'type' => 'percentage',
            'value' => 15,
            'min_spend' => 30.00,
            'description' => '15% off summer groceries',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/coupons');
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('coupons', [
            'code' => 'SUMMERDEAL15',
            'type' => 'percentage',
            'value' => 15,
            'is_active' => true,
        ]);
    }

    /**
     * Test admin can update coupon.
     */
    public function test_admin_can_update_coupon(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'OLDCODE10',
            'value' => 10,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/coupons/{$coupon->id}", [
            'code' => 'NEWCODE25',
            'type' => 'fixed',
            'value' => 25.00,
            'min_spend' => 100.00,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/coupons');
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'NEWCODE25',
            'type' => 'fixed',
            'value' => 25.00,
        ]);
    }

    /**
     * Test AJAX toggle coupon status.
     */
    public function test_admin_can_toggle_coupon_status(): void
    {
        $coupon = Coupon::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->postJson("/admin/coupons/{$coupon->id}/toggle-status");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
        $this->assertFalse($coupon->fresh()->is_active);
    }

    /**
     * Test admin can delete coupon.
     */
    public function test_admin_can_delete_coupon(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'DELETECODE']);

        $response = $this->actingAs($this->admin)->delete("/admin/coupons/{$coupon->id}");

        $response->assertRedirect('/admin/coupons');
        $response->assertSessionHas('toast_success');
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }
}
