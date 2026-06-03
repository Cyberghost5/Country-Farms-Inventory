<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceDetailsPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $generalManager;
    protected User $distributor1;
    protected User $distributor2;
    protected Invoice $invoice1;
    protected Invoice $invoice2;

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

        $this->distributor1 = User::factory()->create([
            'role' => User::ROLE_DISTRIBUTOR,
            'company_name' => 'Alpha Distributors',
            'is_active' => true,
        ]);

        $this->distributor2 = User::factory()->create([
            'role' => User::ROLE_DISTRIBUTOR,
            'company_name' => 'Beta Distributors',
            'is_active' => true,
        ]);

        // Create Invoices
        $this->invoice1 = Invoice::create([
            'distributor_id' => $this->distributor1->id,
            'invoice_number' => 'INV-2026-001',
            'total_amount' => 50000.00,
            'due_amount' => 50000.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(30)->toDateString(),
        ]);

        $this->invoice2 = Invoice::create([
            'distributor_id' => $this->distributor2->id,
            'invoice_number' => 'INV-2026-002',
            'total_amount' => 80000.00,
            'due_amount' => 80000.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(30)->toDateString(),
        ]);
    }

    /** @test */
    public function test_super_admin_can_access_any_invoice_details()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('invoices.show', $this->invoice1->id));

        $response->assertStatus(200);
        $response->assertSee('Invoice Details');
        $response->assertSee('INV-2026-001');
        $response->assertSee('Alpha Distributors');
        $response->assertSee('Record Payment'); // Super Admin can see record payment form
    }

    /** @test */
    public function test_general_manager_can_access_any_invoice_details()
    {
        $response = $this->actingAs($this->generalManager)
            ->get(route('invoices.show', $this->invoice2->id));

        $response->assertStatus(200);
        $response->assertSee('Invoice Details');
        $response->assertSee('INV-2026-002');
        $response->assertSee('Beta Distributors');
        $response->assertDontSee('Record Payment'); // GM cannot record payments (read-only)
    }

    /** @test */
    public function test_distributor_can_access_own_invoice_details()
    {
        $response = $this->actingAs($this->distributor1)
            ->get(route('invoices.show', $this->invoice1->id));

        $response->assertStatus(200);
        $response->assertSee('Invoice Details');
        $response->assertSee('INV-2026-001');
        $response->assertSee('Submit Payment Proof'); // Distributor can submit proof
    }

    /** @test */
    public function test_distributor_cannot_access_other_distributors_invoice()
    {
        $response = $this->actingAs($this->distributor1)
            ->get(route('invoices.show', $this->invoice2->id));

        $response->assertStatus(403);
    }
}
