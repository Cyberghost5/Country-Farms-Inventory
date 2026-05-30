<?php

namespace Tests\Feature;

use App\Models\StateDiscount;
use App\Models\DistributorPricing;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\User;
use App\Notifications\DispatchCompletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DispatchDistributionTest extends TestCase
{
    use RefreshDatabase;

    protected User $storeManager;
    protected User $distributor;
    protected User $superAdmin;
    protected Product $yoghurt;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Users
        $this->storeManager = User::factory()->create([
            'role' => User::ROLE_STORE_MANAGER,
            'is_active' => true,
        ]);

        $this->distributor = User::factory()->create([
            'role' => User::ROLE_DISTRIBUTOR,
            'is_active' => true,
            'company_name' => 'Mega Dist Co.',
            'state' => 'Lagos',
        ]);

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        // Create Product
        $this->yoghurt = Product::create([
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

        // Upload and verify some stock (Total: 500 units)
        $batch = ProductionBatch::create([
            'product_id' => $this->yoghurt->id,
            'quantity' => 500,
            'batch_number' => 'B-01',
            'production_date' => now()->toDateString(),
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'uploaded_by' => $this->superAdmin->id,
            'is_verified' => true,
            'verified_by' => $this->storeManager->id,
            'verified_at' => now(),
        ]);
    }

    /** @test */
    public function test_distributor_pricing_and_discount_resolution()
    {
        // 1. Base price is 1000.00. No custom pricing or discounts.
        $this->assertEquals(1000.00, $this->yoghurt->calculatedPriceForDistributor($this->distributor->id));

        // 2. Set Custom pricing to 900.00
        DistributorPricing::create([
            'distributor_id' => $this->distributor->id,
            'product_id' => $this->yoghurt->id,
            'price' => 900.00,
        ]);
        $this->assertEquals(900.00, $this->yoghurt->calculatedPriceForDistributor($this->distributor->id));

        // 3. Add 10% discount on yoghurt category
        StateDiscount::create([
            'state' => 'Lagos',
            'type' => 'percentage',
            'value' => 10.00,
            'applies_to' => 'category',
            'applies_value' => 'yoghurt',
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);
        // 900 - 10% = 810.00
        $this->assertEquals(810.00, $this->yoghurt->calculatedPriceForDistributor($this->distributor->id));

        // 4. Add fixed discount of 10.00 on all products
        StateDiscount::create([
            'state' => 'Lagos',
            'type' => 'fixed',
            'value' => 10.00,
            'applies_to' => 'all',
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);
        // 810 - 10 = 800.00
        $this->assertEquals(800.00, $this->yoghurt->calculatedPriceForDistributor($this->distributor->id));
    }

    /** @test */
    public function test_store_manager_can_dispatch_valid_stock()
    {
        Notification::fake();

        $response = $this->actingAs($this->storeManager)
            ->post(route('store.dispatches.store'), [
                'distributor_id' => $this->distributor->id,
                'items' => [
                    [
                        'product_id' => $this->yoghurt->id,
                        'quantity' => 100,
                    ]
                ],
                'remarks' => 'First dispatch',
            ]);

        $response->assertStatus(302);
        
        // Assert dispatch was created
        $this->assertDatabaseHas('dispatches', [
            'distributor_id' => $this->distributor->id,
            'total_amount' => 100000.00, // 100 * 1000.00 base price
            'dispatched_by' => $this->storeManager->id,
        ]);

        // Assert stock level decremented
        $this->assertEquals(400, $this->yoghurt->verifiedStock());

        // Assert Invoice auto-generated
        $this->assertDatabaseHas('invoices', [
            'distributor_id' => $this->distributor->id,
            'total_amount' => 100000.00,
            'due_amount' => 100000.00,
            'status' => 'unpaid',
        ]);

        // Assert notification fired
        Notification::assertSentTo(
            $this->distributor,
            DispatchCompletedNotification::class
        );
        Notification::assertSentTo(
            $this->superAdmin,
            DispatchCompletedNotification::class
        );
    }

    /** @test */
    public function test_store_manager_cannot_dispatch_excess_stock()
    {
        $response = $this->actingAs($this->storeManager)
            ->post(route('store.dispatches.store'), [
                'distributor_id' => $this->distributor->id,
                'items' => [
                    [
                        'product_id' => $this->yoghurt->id,
                        'quantity' => 600, // available is 500
                    ]
                ],
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('dispatches', [
            'distributor_id' => $this->distributor->id,
        ]);

        // Stock remains 500
        $this->assertEquals(500, $this->yoghurt->verifiedStock());
    }

    /** @test */
    public function test_distributor_can_confirm_receipt()
    {
        $response = $this->actingAs($this->storeManager)
            ->post(route('store.dispatches.store'), [
                'distributor_id' => $this->distributor->id,
                'items' => [
                    [
                        'product_id' => $this->yoghurt->id,
                        'quantity' => 100,
                    ]
                ],
            ]);

        $dispatch = \App\Models\Dispatch::first();

        // Mark as received
        $response = $this->actingAs($this->distributor)
            ->post(route('distributor.received.receive', $dispatch->id));

        $response->assertStatus(302);
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'status' => 'received',
        ]);
    }

    /** @test */
    public function test_super_admin_can_record_payment()
    {
        Storage::fake('public');
        $file1 = UploadedFile::fake()->create('proof1.pdf', 500, 'application/pdf');
        $file2 = UploadedFile::fake()->create('proof2.png', 300, 'image/png');

        // 1. Dispatch to generate invoice
        $this->actingAs($this->storeManager)
            ->post(route('store.dispatches.store'), [
                'distributor_id' => $this->distributor->id,
                'items' => [
                    [
                        'product_id' => $this->yoghurt->id,
                        'quantity' => 100,
                    ]
                ],
            ]);

        $invoice = Invoice::first();

        // 2. Record partial payment (₦40,000 against ₦100,000 invoice)
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.invoices.payment', $invoice->id), [
                'amount' => 40000.00,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'bank_transfer',
                'proof_of_payment' => $file1,
            ]);

        $response->assertStatus(302);
        
        $expectedPath1 = 'proofs/' . $file1->hashName();

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 40000.00,
            'reference' => $expectedPath1,
        ]);

        Storage::disk('public')->assertExists($expectedPath1);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'due_amount' => 60000.00,
            'status' => 'partially_paid',
        ]);

        // 3. Record final payment (₦60,000)
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.invoices.payment', $invoice->id), [
                'amount' => 60000.00,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'bank_transfer',
                'proof_of_payment' => $file2,
            ]);

        $expectedPath2 = 'proofs/' . $file2->hashName();

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 60000.00,
            'reference' => $expectedPath2,
        ]);

        Storage::disk('public')->assertExists($expectedPath2);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'due_amount' => 0.00,
            'status' => 'paid',
        ]);
    }
}
