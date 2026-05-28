<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\User;
use App\Notifications\BatchDeletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuditReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $generalManager;
    protected User $storeManager;
    protected Product $yoghurt;
    protected ProductionBatch $batch;

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

        $this->batch = ProductionBatch::create([
            'product_id' => $this->yoghurt->id,
            'quantity' => 150,
            'batch_number' => 'B-DEL-99',
            'production_date' => now()->toDateString(),
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'uploaded_by' => $this->superAdmin->id,
            'is_verified' => true,
            'verified_by' => $this->storeManager->id,
            'verified_at' => now(),
        ]);
    }

    /** @test */
    public function test_super_admin_can_download_batch_report()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.inventory.download', $this->batch->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="batch_report_B-DEL-99.csv"');
    }

    /** @test */
    public function test_general_manager_cannot_delete_batch()
    {
        $response = $this->actingAs($this->generalManager)
            ->delete(route('admin.inventory.destroy', $this->batch->id), [
                'reason' => 'Unauthorized deletion attempt.',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('production_batches', [
            'id' => $this->batch->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function test_super_admin_can_delete_batch_with_valid_reason()
    {
        Notification::fake();

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('admin.inventory.destroy', $this->batch->id), [
                'reason' => 'Correcting spelling and entry mistake.',
            ]);

        $response->assertStatus(302);

        // Assert soft deleted
        $this->assertSoftDeleted('production_batches', [
            'id' => $this->batch->id,
        ]);

        // Assert logged in audit trail
        $this->assertDatabaseHas('audit_logs', [
            'user_name' => $this->superAdmin->name,
            'action' => 'delete_batch',
            'reason' => 'Correcting spelling and entry mistake.',
        ]);

        // Assert notification fired to oversight
        Notification::assertSentTo(
            $this->generalManager,
            BatchDeletedNotification::class
        );
    }

    /** @test */
    public function test_deletion_fails_without_sufficient_reason()
    {
        $response = $this->actingAs($this->superAdmin)
            ->delete(route('admin.inventory.destroy', $this->batch->id), [
                'reason' => 'Short', // Less than 10 characters
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['reason']);
        
        $this->assertDatabaseHas('production_batches', [
            'id' => $this->batch->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function test_oversight_roles_can_access_reports()
    {
        // 1. GM
        $response = $this->actingAs($this->generalManager)
            ->get(route('admin.reports.index'));
        $response->assertStatus(200);

        // 2. Super Admin
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.reports.index'));
        $response->assertStatus(200);

        // 3. Store Manager (blocked)
        $response = $this->actingAs($this->storeManager)
            ->get(route('admin.reports.index'));
        $response->assertStatus(403);
    }
}
