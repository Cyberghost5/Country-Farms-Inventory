<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $generalManager;
    protected User $distributor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->generalManager = User::factory()->create([
            'role' => User::ROLE_GENERAL_MANAGER,
            'is_active' => true,
        ]);

        $this->distributor = User::factory()->create([
            'role' => User::ROLE_DISTRIBUTOR,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_super_admin_can_delete_user()
    {
        $response = $this->actingAs($this->superAdmin)
            ->delete(route('admin.users.destroy', $this->distributor->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $this->distributor->id,
        ]);
    }

    /** @test */
    public function test_super_admin_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->superAdmin)
            ->delete(route('admin.users.destroy', $this->superAdmin->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'id' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function test_non_super_admin_cannot_delete_user()
    {
        $response = $this->actingAs($this->generalManager)
            ->delete(route('admin.users.destroy', $this->distributor->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'id' => $this->distributor->id,
        ]);
    }

    /** @test */
    public function test_super_admin_can_impersonate_user_and_then_stop()
    {
        // 1. Super Admin triggers impersonate
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', $this->distributor->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('impersonator_id', $this->superAdmin->id);

        // Verify we are now logged in as the distributor
        $this->assertAuthenticatedAs($this->distributor);

        // 2. Accessing dashboard as distributor sees the impersonate banner (check session state)
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('You are currently logged in as');

        // 3. Stop impersonation
        $response = $this->post(route('admin.users.stop-impersonate'));
        $response->assertStatus(302);
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionMissing('impersonator_id');

        // Verify we are logged back in as the original super admin
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    /** @test */
    public function test_cannot_impersonate_self()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', $this->superAdmin->id));

        $response->assertStatus(400);
        $response->assertSessionMissing('impersonator_id');
    }

    /** @test */
    public function test_stop_impersonate_redirects_if_not_impersonating()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.users.stop-impersonate'));

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function test_super_admin_can_create_distributor_with_state_and_lga()
    {
        $userData = [
            'name' => 'New Distributor Test',
            'email' => 'newdist@test.com',
            'phone' => '08099887766',
            'role' => 'distributor',
            'company_name' => 'Test Company',
            'state' => 'Lagos',
            'lga' => 'Ikeja',
            'address' => '123 Test Street',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.users.store'), $userData);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New Distributor Test',
            'phone' => '08099887766',
            'state' => 'Lagos',
            'lga' => 'Ikeja',
        ]);
    }

    /** @test */
    public function test_super_admin_can_update_distributor_with_state_and_lga()
    {
        $updateData = [
            'name' => 'Updated Distributor Test',
            'email' => 'updatedist@test.com',
            'phone' => '08099887766',
            'company_name' => 'Updated Test Company',
            'state' => 'Abuja',
            'lga' => 'Garki',
            'address' => '456 Updated Street',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('admin.users.update', $this->distributor->id), $updateData);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->distributor->id,
            'name' => 'Updated Distributor Test',
            'state' => 'Abuja',
            'lga' => 'Garki',
        ]);
    }
}
