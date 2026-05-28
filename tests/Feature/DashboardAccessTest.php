<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $productionManager;
    protected User $storeManager;
    protected User $distributor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->productionManager = User::factory()->create([
            'role' => User::ROLE_PRODUCTION_MANAGER,
            'is_active' => true,
        ]);

        $this->storeManager = User::factory()->create([
            'role' => User::ROLE_STORE_MANAGER,
            'is_active' => true,
        ]);

        $this->distributor = User::factory()->create([
            'role' => User::ROLE_DISTRIBUTOR,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_super_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Revenue Collected');
        $response->assertSee('Total Outstanding');
    }

    /** @test */
    public function test_production_manager_can_access_dashboard()
    {
        $response = $this->actingAs($this->productionManager)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('My Uploads');
        $response->assertSee('Pending Verification');
    }

    /** @test */
    public function test_store_manager_can_access_dashboard()
    {
        $response = $this->actingAs($this->storeManager)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Stock');
        $response->assertSee('Dispatches');
    }

    /** @test */
    public function test_distributor_can_access_dashboard()
    {
        $response = $this->actingAs($this->distributor)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Products Received');
        $response->assertSee('Outstanding Balance');
    }
}
