<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login screen can be rendered.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in');
    }

    /**
     * Test unauthorized access redirects to login.
     */
    public function test_unauthorized_access_redirects_to_login(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can authenticate with valid credentials.
     */
    public function test_admin_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'testadmin@grocery.local',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'testadmin@grocery.local',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/admin/dashboard');
    }

    /**
     * Test admin cannot authenticate with invalid password.
     */
    public function test_admin_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'testadmin2@grocery.local',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'testadmin2@grocery.local',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test inactive user cannot authenticate.
     */
    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@grocery.local',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'inactive@grocery.local',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test authenticated admin can logout.
     */
    public function test_authenticated_admin_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'testadmin3@grocery.local',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($user)->post('/admin/logout');

        $this->assertGuest();
        $response->assertRedirect('/admin/login');
    }
}
