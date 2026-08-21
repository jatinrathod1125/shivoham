<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOfferTest extends TestCase
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
     * Test guest cannot access offers index.
     */
    public function test_guest_cannot_access_offers(): void
    {
        $response = $this->get('/admin/offers');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view offers index with KPIs.
     */
    public function test_admin_can_view_offers_index(): void
    {
        Offer::factory()->create([
            'title' => 'Fresh Citrus Festival',
            'discount_type' => Offer::TYPE_PERCENTAGE,
            'discount_value' => 25,
            'badge_text' => 'FLASH SALE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/offers');

        $response->assertStatus(200);
        $response->assertSee('Offers & Deals');
        $response->assertSee('Fresh Citrus Festival');
        $response->assertSee('FLASH SALE');
        $response->assertSee('25% OFF');
        $response->assertSee('Total Campaigns');
    }

    /**
     * Test admin can create offer.
     */
    public function test_admin_can_create_offer(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/offers', [
            'title' => 'Weekend Bakery Delight',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'badge_text' => 'WEEKEND ONLY',
            'description' => '15% discount on all artisan pastries and breads',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/offers');
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('offers', [
            'title' => 'Weekend Bakery Delight',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'badge_text' => 'WEEKEND ONLY',
            'is_active' => true,
        ]);
    }

    /**
     * Test admin can update offer.
     */
    public function test_admin_can_update_offer(): void
    {
        $offer = Offer::factory()->create([
            'title' => 'Old Offer Title',
            'discount_value' => 10,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/offers/{$offer->id}", [
            'title' => 'Updated Offer Title',
            'discount_type' => 'fixed',
            'discount_value' => 7.50,
            'badge_text' => 'SAVE $7.50',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/offers');
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('offers', [
            'id' => $offer->id,
            'title' => 'Updated Offer Title',
            'discount_type' => 'fixed',
            'discount_value' => 7.50,
        ]);
    }

    /**
     * Test AJAX toggle offer status.
     */
    public function test_admin_can_toggle_offer_status(): void
    {
        $offer = Offer::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->postJson("/admin/offers/{$offer->id}/toggle-status");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
        $this->assertFalse($offer->fresh()->is_active);
    }

    /**
     * Test admin can delete offer.
     */
    public function test_admin_can_delete_offer(): void
    {
        $offer = Offer::factory()->create(['title' => 'Delete Me Offer']);

        $response = $this->actingAs($this->admin)->delete("/admin/offers/{$offer->id}");

        $response->assertRedirect('/admin/offers');
        $response->assertSessionHas('toast_success');
        $this->assertDatabaseMissing('offers', ['id' => $offer->id]);
    }
}
