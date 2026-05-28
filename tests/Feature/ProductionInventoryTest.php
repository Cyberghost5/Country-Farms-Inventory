<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\User;
use App\Notifications\BatchUploadedNotification;
use App\Notifications\BatchVerifiedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProductionInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $productionManager;
    protected User $storeManager;
    protected User $superAdmin;
    protected Product $activeProduct;
    protected Product $inactiveProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Users
        $this->productionManager = User::factory()->create([
            'role' => User::ROLE_PRODUCTION_MANAGER,
            'is_active' => true,
        ]);

        $this->storeManager = User::factory()->create([
            'role' => User::ROLE_STORE_MANAGER,
            'is_active' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        // Create Products
        $this->activeProduct = Product::create([
            'name' => 'Strawberry Yoghurt 1L',
            'sku' => 'YOG-STR-001',
            'category' => 'yoghurt',
            'size_volume' => '1L',
            'packaging_type' => 'bottle',
            'unit' => 'piece',
            'base_price' => 1200.00,
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);

        $this->inactiveProduct = Product::create([
            'name' => 'Pineapple Yoghurt 250ml',
            'sku' => 'YOG-PIN-002',
            'category' => 'yoghurt',
            'size_volume' => '250ml',
            'packaging_type' => 'cup',
            'unit' => 'piece',
            'base_price' => 400.00,
            'is_active' => false,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function test_production_manager_can_upload_batch_for_active_product()
    {
        Notification::fake();

        $response = $this->actingAs($this->productionManager)
            ->post(route('production.batches.store'), [
                'product_id' => $this->activeProduct->id,
                'quantity' => 100,
                'batch_number' => 'BATCH-STR-01',
                'production_date' => now()->toDateString(),
                'expiry_date' => now()->addMonths(6)->toDateString(),
                'remarks' => 'Fresh batch',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('production_batches', [
            'batch_number' => 'BATCH-STR-01',
            'quantity' => 100,
            'is_verified' => false,
            'uploaded_by' => $this->productionManager->id,
        ]);

        // Assert notification was sent
        Notification::assertSentTo(
            $this->storeManager,
            BatchUploadedNotification::class
        );
        Notification::assertSentTo(
            $this->superAdmin,
            BatchUploadedNotification::class
        );
    }

    /** @test */
    public function test_production_manager_cannot_upload_batch_for_inactive_product()
    {
        $response = $this->actingAs($this->productionManager)
            ->post(route('production.batches.store'), [
                'product_id' => $this->inactiveProduct->id,
                'quantity' => 100,
                'batch_number' => 'BATCH-PIN-01',
                'production_date' => now()->toDateString(),
                'expiry_date' => now()->addMonths(6)->toDateString(),
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('production_batches', [
            'batch_number' => 'BATCH-PIN-01',
        ]);
    }

    /** @test */
    public function test_production_manager_can_edit_unverified_batch()
    {
        $batch = ProductionBatch::create([
            'product_id' => $this->activeProduct->id,
            'quantity' => 100,
            'batch_number' => 'BATCH-STR-02',
            'production_date' => now()->toDateString(),
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'uploaded_by' => $this->productionManager->id,
            'is_verified' => false,
        ]);

        $response = $this->actingAs($this->productionManager)
            ->put(route('production.batches.update', $batch->id), [
                'product_id' => $this->activeProduct->id,
                'quantity' => 150,
                'batch_number' => 'BATCH-STR-02-UPDATED',
                'production_date' => now()->toDateString(),
                'expiry_date' => now()->addMonths(6)->toDateString(),
                'remarks' => 'Updated qty',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('production_batches', [
            'id' => $batch->id,
            'quantity' => 150,
            'batch_number' => 'BATCH-STR-02-UPDATED',
        ]);
    }

    /** @test */
    public function test_production_manager_cannot_edit_verified_batch()
    {
        $batch = ProductionBatch::create([
            'product_id' => $this->activeProduct->id,
            'quantity' => 100,
            'batch_number' => 'BATCH-STR-03',
            'production_date' => now()->toDateString(),
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'uploaded_by' => $this->productionManager->id,
            'is_verified' => true,
            'verified_by' => $this->storeManager->id,
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($this->productionManager)
            ->put(route('production.batches.update', $batch->id), [
                'product_id' => $this->activeProduct->id,
                'quantity' => 150,
                'batch_number' => 'BATCH-STR-03-UPDATED',
                'production_date' => now()->toDateString(),
                'expiry_date' => now()->addMonths(6)->toDateString(),
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('production_batches', [
            'id' => $batch->id,
            'quantity' => 100,
            'batch_number' => 'BATCH-STR-03',
        ]);
    }

    /** @test */
    public function test_store_manager_can_verify_batch()
    {
        Notification::fake();

        $batch = ProductionBatch::create([
            'product_id' => $this->activeProduct->id,
            'quantity' => 200,
            'batch_number' => 'BATCH-STR-04',
            'production_date' => now()->toDateString(),
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'uploaded_by' => $this->productionManager->id,
            'is_verified' => false,
        ]);

        $response = $this->actingAs($this->storeManager)
            ->post(route('store.inventory.verify', $batch->id));

        $response->assertStatus(302);
        $this->assertDatabaseHas('production_batches', [
            'id' => $batch->id,
            'is_verified' => true,
            'verified_by' => $this->storeManager->id,
        ]);

        // Verify product verified stock updates
        $this->assertEquals(200, $this->activeProduct->verifiedStock());

        // Assert notification was sent to superAdmin
        Notification::assertSentTo(
            $this->superAdmin,
            BatchVerifiedNotification::class
        );
    }
}
