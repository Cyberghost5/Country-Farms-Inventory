<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Dispatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DistributorPaymentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $distributor;
    protected User $superAdmin;
    protected User $generalManager;
    protected Invoice $invoice;

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

        // Create an Invoice
        $this->invoice = Invoice::create([
            'distributor_id' => $this->distributor->id,
            'invoice_number' => 'INV-2026-0001',
            'total_amount' => 100000.00,
            'due_amount' => 100000.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(14)->toDateString(),
        ]);
    }

    /** @test */
    public function test_distributor_can_upload_payment_proof()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('proof.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->distributor)
            ->post(route('distributor.payments.upload'), [
                'invoice_id' => $this->invoice->id,
                'amount' => 40000.00,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'bank_transfer',
                'proof_of_payment' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Get expected stored path
        $expectedPath = 'proofs/' . $file->hashName();

        // Verify payment record was created in pending status with reference as path
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'amount' => 40000.00,
            'status' => 'pending',
            'reference' => $expectedPath,
            'recorded_by' => $this->distributor->id,
        ]);

        // Verify file is stored in fake disk
        Storage::disk('public')->assertExists($expectedPath);

        // Outstanding balance should NOT be decremented yet
        $this->invoice->refresh();
        $this->assertEquals(100000.00, $this->invoice->due_amount);
        $this->assertEquals('pending_approval', $this->invoice->status);
    }

    /** @test */
    public function test_super_admin_can_approve_payment()
    {
        // 1. Distributor uploads a payment
        $payment = Payment::create([
            'invoice_id' => $this->invoice->id,
            'distributor_id' => $this->distributor->id,
            'payment_number' => 'PAY-20260528-0001',
            'amount' => 40000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference' => 'REF-998877',
            'recorded_by' => $this->distributor->id,
            'status' => 'pending',
        ]);

        // 2. Super Admin approves it
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.payments.approve', $payment->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Status is now approved
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'approved',
        ]);

        // Invoice balance is decremented
        $this->invoice->refresh();
        $this->assertEquals(60000.00, $this->invoice->due_amount);
        $this->assertEquals('partially_paid', $this->invoice->status);
    }

    /** @test */
    public function test_super_admin_can_reject_payment()
    {
        // 1. Distributor uploads a payment
        $payment = Payment::create([
            'invoice_id' => $this->invoice->id,
            'distributor_id' => $this->distributor->id,
            'payment_number' => 'PAY-20260528-0001',
            'amount' => 40000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference' => 'REF-998877',
            'recorded_by' => $this->distributor->id,
            'status' => 'pending',
        ]);

        // 2. Super Admin rejects it
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.payments.reject', $payment->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Status is now rejected
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'rejected',
        ]);

        // Invoice balance remains unchanged
        $this->invoice->refresh();
        $this->assertEquals(100000.00, $this->invoice->due_amount);
        $this->assertEquals('unpaid', $this->invoice->status);
    }

    /** @test */
    public function test_general_manager_cannot_approve_payment()
    {
        $payment = Payment::create([
            'invoice_id' => $this->invoice->id,
            'distributor_id' => $this->distributor->id,
            'payment_number' => 'PAY-20260528-0001',
            'amount' => 40000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'recorded_by' => $this->distributor->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->generalManager)
            ->post(route('admin.payments.approve', $payment->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_reject_reverts_invoice_to_partially_paid()
    {
        // Setup partially paid invoice
        $this->invoice->update([
            'due_amount' => 60000.00,
            'status' => 'partially_paid',
        ]);

        $payment = Payment::create([
            'invoice_id' => $this->invoice->id,
            'distributor_id' => $this->distributor->id,
            'payment_number' => 'PAY-20260528-0002',
            'amount' => 30000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference' => 'proofs/dummy.pdf',
            'recorded_by' => $this->distributor->id,
            'status' => 'pending',
        ]);

        // Invoice status is manually set to pending_approval in real app
        $this->invoice->update(['status' => 'pending_approval']);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.payments.reject', $payment->id));

        $response->assertStatus(302);

        $this->invoice->refresh();
        $this->assertEquals(60000.00, $this->invoice->due_amount);
        $this->assertEquals('partially_paid', $this->invoice->status);
    }

    /** @test */
    public function test_distributor_cannot_upload_payment_if_pending_approval()
    {
        $this->invoice->update(['status' => 'pending_approval']);

        Storage::fake('public');
        $file = UploadedFile::fake()->create('proof.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->distributor)
            ->post(route('distributor.payments.upload'), [
                'invoice_id' => $this->invoice->id,
                'amount' => 40000.00,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'bank_transfer',
                'proof_of_payment' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'A payment proof has already been submitted for this invoice and is awaiting approval.');
    }
}
