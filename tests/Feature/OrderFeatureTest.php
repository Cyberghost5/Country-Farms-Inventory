<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $generalManager;
    protected User $storeManager;
    protected User $productionManager;
    protected User $distributor1;
    protected User $distributor2;
    protected Product $product;

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

        $this->storeManager = User::factory()->create([
            'role' => User::ROLE_STORE_MANAGER,
            'is_active' => true,
        ]);

        $this->productionManager = User::factory()->create([
            'role' => User::ROLE_PRODUCTION_MANAGER,
            'is_active' => true,
        ]);

        $this->distributor1 = User::factory()->create([
            'role' => User::ROLE_DISTRIBUTOR,
            'is_active' => true,
        ]);

        $this->distributor2 = User::factory()->create([
            'role' => User::ROLE_DISTRIBUTOR,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Strawberry Yoghurt Pack',
            'sku' => 'YOG-STRAW-1111',
            'category' => 'yoghurt',
            'size_volume' => '250ml',
            'packaging_type' => 'bottle',
            'unit' => 'piece',
            'base_price' => 500.00,
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function test_distributor_can_place_an_order()
    {
        $orderData = [
            'remarks' => 'Deliver by Saturday morning please',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                ]
            ]
        ];

        $response = $this->actingAs($this->distributor1)
            ->post(route('orders.store'), $orderData);

        $response->assertStatus(302);
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'distributor_id' => $this->distributor1->id,
            'status' => 'pending',
            'total_amount' => 5000.00, // 500.00 * 10
            'remarks' => 'Deliver by Saturday morning please',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 500.00,
            'subtotal' => 5000.00,
        ]);
    }

    /** @test */
    public function test_distributor_can_only_see_their_own_orders()
    {
        // Order for distributor 1
        $order1 = Order::create([
            'distributor_id' => $this->distributor1->id,
            'order_number' => 'ORD-20260530-0001',
            'status' => 'pending',
            'total_amount' => 1000.00,
        ]);

        // Order for distributor 2
        $order2 = Order::create([
            'distributor_id' => $this->distributor2->id,
            'order_number' => 'ORD-20260530-0002',
            'status' => 'pending',
            'total_amount' => 2000.00,
        ]);

        // Distributor 1 index request
        $response1 = $this->actingAs($this->distributor1)->get(route('orders.index'));
        $response1->assertStatus(200);
        $response1->assertSee($order1->order_number);
        $response1->assertDontSee($order2->order_number);

        // Distributor 1 show request of distributor 2 order
        $response2 = $this->actingAs($this->distributor1)->get(route('orders.show', $order2->id));
        $response2->assertStatus(404); // Should fail to find since it is scoped
    }

    /** @test */
    public function test_managers_and_oversight_roles_can_see_all_orders()
    {
        $order = Order::create([
            'distributor_id' => $this->distributor1->id,
            'order_number' => 'ORD-20260530-0001',
            'status' => 'pending',
            'total_amount' => 1000.00,
        ]);

        // General Manager
        $response = $this->actingAs($this->generalManager)->get(route('orders.index'));
        $response->assertStatus(200);
        $response->assertSee($order->order_number);

        // Store Manager
        $response = $this->actingAs($this->storeManager)->get(route('orders.index'));
        $response->assertStatus(200);
        $response->assertSee($order->order_number);

        // Production Manager
        $response = $this->actingAs($this->productionManager)->get(route('orders.index'));
        $response->assertStatus(200);
        $response->assertSee($order->order_number);
    }

    /** @test */
    public function test_super_admin_and_general_manager_can_approve_and_reject_orders()
    {
        $order = Order::create([
            'distributor_id' => $this->distributor1->id,
            'order_number' => 'ORD-20260530-0001',
            'status' => 'pending',
            'total_amount' => 1000.00,
        ]);

        // Super Admin approves
        $response = $this->actingAs($this->superAdmin)
            ->post(route('orders.approve', $order->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('orders.show', $order->id));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'approved',
            'processed_by' => $this->superAdmin->id,
        ]);

        // Reset to pending
        $order->refresh();
        $order->update(['status' => 'pending', 'processed_by' => null]);

        // General Manager rejects
        $response = $this->actingAs($this->generalManager)
            ->post(route('orders.reject', $order->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('orders.show', $order->id));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'rejected',
            'processed_by' => $this->generalManager->id,
        ]);
    }

    /** @test */
    public function test_non_approvers_cannot_approve_or_reject_orders()
    {
        $order = Order::create([
            'distributor_id' => $this->distributor1->id,
            'order_number' => 'ORD-20260530-0001',
            'status' => 'pending',
            'total_amount' => 1000.00,
        ]);

        // Store Manager tries to approve
        $response = $this->actingAs($this->storeManager)
            ->post(route('orders.approve', $order->id));
        $response->assertStatus(403);

        // Distributor tries to approve
        $response = $this->actingAs($this->distributor1)
            ->post(route('orders.approve', $order->id));
        $response->assertStatus(403);

        // Order status remains pending
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
            'processed_by' => null,
        ]);
    }
}
