<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\DistributorDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributorDiscountStoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $distributor;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->distributor = User::factory()->create([
            'role' => User::ROLE_DISTRIBUTOR,
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'name' => 'Strawberry Yoghurt 1L',
            'sku' => 'YOG-STR-01',
            'category' => 'yoghurt',
            'size_volume' => '1L',
            'packaging_type' => 'bottle',
            'unit' => 'piece',
            'base_price' => 1000.00,
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);

        $this->product2 = Product::create([
            'name' => 'Apple Drink 500ml',
            'sku' => 'DRK-APL-02',
            'category' => 'drink',
            'size_volume' => '500ml',
            'packaging_type' => 'bottle',
            'unit' => 'piece',
            'base_price' => 500.00,
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function test_store_discount_for_all_products()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.pricing.storeDiscount'), [
                'distributor_id' => $this->distributor->id,
                'type' => 'percentage',
                'value' => 10.00,
                'applies_to' => 'all',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('distributor_discounts', [
            'distributor_id' => $this->distributor->id,
            'type' => 'percentage',
            'value' => 10.00,
            'applies_to' => 'all',
            'applies_value' => null,
        ]);
    }

    /** @test */
    public function test_store_discount_for_multiple_categories()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.pricing.storeDiscount'), [
                'distributor_id' => $this->distributor->id,
                'type' => 'percentage',
                'value' => 15.00,
                'applies_to' => 'category',
                'applies_value_categories' => ['yoghurt', 'drink'],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('distributor_discounts', [
            'distributor_id' => $this->distributor->id,
            'type' => 'percentage',
            'value' => 15.00,
            'applies_to' => 'category',
            'applies_value' => 'yoghurt',
        ]);

        $this->assertDatabaseHas('distributor_discounts', [
            'distributor_id' => $this->distributor->id,
            'type' => 'percentage',
            'value' => 15.00,
            'applies_to' => 'category',
            'applies_value' => 'drink',
        ]);
    }

    /** @test */
    public function test_store_discount_for_multiple_products()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.pricing.storeDiscount'), [
                'distributor_id' => $this->distributor->id,
                'type' => 'fixed',
                'value' => 50.00,
                'applies_to' => 'product',
                'applies_value_products' => [$this->product1->id, $this->product2->id],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('distributor_discounts', [
            'distributor_id' => $this->distributor->id,
            'type' => 'fixed',
            'value' => 50.00,
            'applies_to' => 'product',
            'applies_value' => $this->product1->id,
        ]);

        $this->assertDatabaseHas('distributor_discounts', [
            'distributor_id' => $this->distributor->id,
            'type' => 'fixed',
            'value' => 50.00,
            'applies_to' => 'product',
            'applies_value' => $this->product2->id,
        ]);
    }

    /** @test */
    public function test_discounts_page_resolves_product_names()
    {
        // 1. Create a product discount
        DistributorDiscount::create([
            'distributor_id' => $this->distributor->id,
            'type' => 'percentage',
            'value' => 20.00,
            'applies_to' => 'product',
            'applies_value' => $this->product1->id,
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);

        // 2. View pricing index page for this distributor
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.pricing.index', ['distributor_id' => $this->distributor->id]));

        $response->assertStatus(200);
        // Verify the product name is rendered in the Applies To column
        $response->assertSee('Strawberry Yoghurt 1L');
    }
}
