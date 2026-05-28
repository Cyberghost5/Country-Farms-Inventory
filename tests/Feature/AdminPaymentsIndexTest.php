<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentsIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $generalManager;
    protected User $distributor1;
    protected User $distributor2;
    protected Invoice $invoice1;
    protected Invoice $invoice2;
    protected Payment $paymentPending;
    protected Payment $paymentApproved;

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
            'invoice_number' => 'INV-001',
            'total_amount' => 50000.00,
            'due_amount' => 50000.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $this->invoice2 = Invoice::create([
            'distributor_id' => $this->distributor2->id,
            'invoice_number' => 'INV-002',
            'total_amount' => 80000.00,
            'due_amount' => 80000.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        // Create Payments
        $this->paymentPending = Payment::create([
            'invoice_id' => $this->invoice1->id,
            'distributor_id' => $this->distributor1->id,
            'payment_number' => 'PAY-001',
            'amount' => 20000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference' => 'proofs/dummy1.pdf',
            'recorded_by' => $this->distributor1->id,
            'status' => 'pending',
        ]);

        $this->paymentApproved = Payment::create([
            'invoice_id' => $this->invoice2->id,
            'distributor_id' => $this->distributor2->id,
            'payment_number' => 'PAY-002',
            'amount' => 80000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'reference' => 'proofs/dummy2.png',
            'recorded_by' => $this->superAdmin->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function test_oversight_users_can_access_admin_payments_page()
    {
        // Super Admin can access
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Payments Log');
        $response->assertSee('PAY-001');
        $response->assertSee('PAY-002');
        $response->assertSee('Alpha Distributors');
        $response->assertSee('Beta Distributors');

        // General Manager can access
        $response = $this->actingAs($this->generalManager)
            ->get(route('admin.payments.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_distributors_cannot_access_admin_payments_page()
    {
        $response = $this->actingAs($this->distributor1)
            ->get(route('admin.payments.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_admin_can_filter_payments_by_status()
    {
        // Filter by Pending
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.payments.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('PAY-001');
        $response->assertDontSee('PAY-002');

        // Filter by Approved
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.payments.index', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertDontSee('PAY-001');
        $response->assertSee('PAY-002');
    }

    /** @test */
    public function test_admin_can_filter_payments_by_distributor()
    {
        // Filter by distributor 1
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.payments.index', ['distributor_id' => $this->distributor1->id]));

        $response->assertStatus(200);
        $response->assertSee('PAY-001');
        $response->assertDontSee('PAY-002');

        // Filter by distributor 2
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.payments.index', ['distributor_id' => $this->distributor2->id]));

        $response->assertStatus(200);
        $response->assertDontSee('PAY-001');
        $response->assertSee('PAY-002');
    }
}
