<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Distributors Financials - Country Yoghurt MD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">@include('partials._sidebar')</aside>

      <main class="main-content">
        <header class="topbar">
          <div class="title-block">
            <h2>Distributors Financials</h2>
            <p>Oversight panel — monitor distributor sales, balances, and record payments.</p>
          </div>
          @if ($user->isSuperAdmin() && $unpaidInvoices->isNotEmpty())
            <div class="top-actions">
              <button class="primary-btn" id="openPaymentModal" style="background:#2e7d32; border-color:#2e7d32;">
                <i class="bi bi-credit-card"></i> Record Payment
              </button>
            </div>
          @endif
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- KPI bar --}}
        <section class="kpi-grid" style="margin-bottom:24px;">
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#fdecea;color:#b71c1c;"><i class="bi bi-wallet2"></i></span></div>
            <h4 class="stat-value" style="color:#b71c1c;">₦{{ number_format($stats['total_outstanding'], 2) }}</h4>
            <small class="stat-label">Total Outstanding Balance</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="bi bi-currency-dollar"></i></span></div>
            <h4 class="stat-value" style="color:#2e7d32;">₦{{ number_format($stats['total_revenue'], 2) }}</h4>
            <small class="stat-label">Total Revenue Collected</small>
          </article>
        </section>

        {{-- Pending Payments Approvals --}}
        @if ($user->isSuperAdmin() && $pendingPayments->isNotEmpty())
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 1rem; color: #1d086c; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
              <i class="bi bi-hourglass-split"></i> Pending Payments Approvals
            </h3>
            <section class="card table-card">
              <div class="table-scroll">
                <table class="inv-table">
                  <thead>
                    <tr>
                      <th>Payment Number</th>
                      <th>Distributor</th>
                      <th>Invoice Number</th>
                      <th>Amount Paid (₦)</th>
                      <th>Payment Date</th>
                      <th>Method</th>
                      <th>Proof of Payment</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($pendingPayments as $p)
                      <tr>
                        <td><strong style="color:#1d086c;">{{ $p->payment_number }}</strong></td>
                        <td>{{ $p->distributor->company_name ?: $p->distributor->name }}</td>
                        <td><code>{{ $p->invoice->invoice_number }}</code></td>
                        <td><strong style="color:#2e7d32;">₦{{ number_format($p->amount, 2) }}</strong></td>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                        <td><span class="cat-pill cat-others">{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</span></td>
                        <td>
                          @if ($p->reference)
                            <a href="{{ asset('storage/' . $p->reference) }}" target="_blank" class="ghost-btn btn-sm" style="color: var(--primary, #3a6b35); padding: 4px 10px; font-size: 0.75rem; text-decoration: none;">
                              <i class="bi bi-file-earmark-arrow-down"></i> View Proof
                            </a>
                          @else
                            —
                          @endif
                        </td>
                        <td>
                          <div style="display: flex; gap: 8px;">
                            <form method="POST" action="{{ route('admin.payments.approve', $p->id) }}" style="display:inline;">
                              @csrf
                              <button type="submit" class="primary-btn btn-sm" style="background:#2e7d32; border-color:#2e7d32; padding: 4px 10px; font-size: 0.75rem;">
                                <i class="bi bi-check-lg"></i> Approve
                              </button>
                            </form>
                            <form method="POST" action="{{ route('admin.payments.reject', $p->id) }}" style="display:inline;"
                                  onsubmit="return confirm('Are you sure you want to reject this payment?')">
                              @csrf
                              <button type="submit" class="danger-ghost btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">
                                <i class="bi bi-x-lg"></i> Reject
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </section>
          </div>
        @endif

        {{-- Table --}}
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Distributor</th>
                  <th>Company Details</th>
                  <th>Total Invoiced (₦)</th>
                  <th>Total Paid (₦)</th>
                  <th>Outstanding Balance (₦)</th>
                  <th>Status</th>
                  <th>Statements Log</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($distributors as $d)
                  @php
                    $totalInvoiced = $d->invoices()->sum('total_amount');
                    $totalPaid = $d->payments()->where('status', 'approved')->sum('amount');
                    $outstanding = $totalInvoiced - $totalPaid;
                  @endphp
                  <tr>
                    <td>
                      <span class="inv-name">{{ $d->name }}</span>
                      <small style="color:#666; display:block;">{{ $d->phone }} &middot; {{ $d->email ?: 'No Email' }}</small>
                    </td>
                    <td>
                      <span style="font-weight:600; color:#1d086c;">{{ $d->company_name ?: '—' }}</span>
                      <small style="color:#666; display:block;">{{ $d->state ?: 'No State' }} &middot; {{ $d->address ?: 'No Address' }}</small>
                    </td>
                    <td>{{ number_format($totalInvoiced, 2) }}</td>
                    <td>{{ number_format($totalPaid, 2) }}</td>
                    <td>
                      @if ($outstanding > 0)
                        <strong style="color:#b71c1c;">₦{{ number_format($outstanding, 2) }}</strong>
                      @else
                        <span style="color:#2e7d32; font-weight:600;">₦0.00</span>
                      @endif
                    </td>
                    <td>
                      @if ($d->is_active)
                        <span class="status-badge badge-active">Active</span>
                      @else
                        <span class="status-badge badge-inactive">Inactive</span>
                      @endif
                    </td>
                    <td>
                      <details style="cursor:pointer; font-size:0.9rem; color:#1d086c;">
                        <summary style="font-weight:500;">Statements ({{ $d->invoices->count() }} Invoices)</summary>
                        <div style="margin-top:6px; background:#f8f9fa; padding:8px; border-radius:6px; border:1px solid #eee; color:#333; line-height:1.4;">
                          <div style="font-weight:600; border-bottom:1px solid #ddd; padding-bottom:4px; margin-bottom:6px;">Invoices List:</div>
                          @forelse ($d->invoices as $inv)
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:0.8rem;">
                              <span>{{ $inv->invoice_number }} (Due: {{ $inv->due_date->format('d M Y') }})</span>
                              <span style="font-weight:600;" class="{{ $inv->status === 'paid' ? 'badge-active' : ($inv->status === 'partially_paid' ? 'badge-inactive' : 'stock-badge-low') }}">
                                {{ number_format($inv->due_amount, 2) }} due
                              </span>
                            </div>
                          @empty
                            <div style="color:#999; font-size:0.8rem;">No invoices logged.</div>
                          @endforelse
                        </div>
                      </details>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="empty-row">No distributors registered in the system.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if ($distributors->hasPages())
            <div class="pagination-wrap">{{ $distributors->links() }}</div>
          @endif
        </section>
      </main>
    </div>

    {{-- ══════════ RECORD PAYMENT MODAL ══════════ --}}
    @if ($user->isSuperAdmin() && $unpaidInvoices->isNotEmpty())
      <div class="inv-modal-overlay" id="paymentModal">
        <div class="inv-modal">
          <div class="inv-modal-head">
            <h3>Record Payment Received</h3>
            <button class="inv-modal-close" id="closePaymentModal" type="button"><i class="bi bi-x-lg"></i></button>
          </div>
          <form method="POST" action="" id="paymentForm" class="inv-modal-form" enctype="multipart/form-data">
            @csrf
            <div class="inv-modal-body">
              <div class="form-group">
                <label for="invoice_id">Select Unpaid Invoice *</label>
                <select class="form-input" id="invoice_id" name="invoice_id" required>
                  <option value="">-- Choose Invoice --</option>
                  @foreach ($unpaidInvoices as $ui)
                    @php
                      $distName = $ui->distributor->company_name ?: $ui->distributor->name;
                    @endphp
                    <option value="{{ $ui->id }}" data-due="{{ $ui->due_amount }}">
                      {{ $distName }} &middot; {{ $ui->invoice_number }} (Due: ₦{{ number_format($ui->due_amount, 2) }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="form-row-2">
                <div class="form-group">
                  <label for="amount">Payment Amount (₦) *</label>
                  <input class="form-input" type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder="e.g. 50000" />
                </div>
                <div class="form-group">
                  <label for="payment_date">Payment Date *</label>
                  <input class="form-input" type="date" id="payment_date" name="payment_date" required value="{{ now()->toDateString() }}" />
                </div>
              </div>

              <div class="form-row-2">
                <div class="form-group">
                  <label for="payment_method">Payment Method *</label>
                  <select class="form-input" id="payment_method" name="payment_method" required>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="upload_proof">Proof of Payment * (Image or PDF only)</label>
                  <input class="form-input" type="file" id="upload_proof" name="proof_of_payment" accept="image/*,application/pdf" required />
                </div>
              </div>
            </div>
            <div class="inv-modal-footer">
              <button type="button" class="ghost-btn" id="cancelPaymentModal">Cancel</button>
              <button type="submit" class="primary-btn" style="background:#2e7d32; border-color:#2e7d32;"><i class="bi bi-check-lg"></i> Record Payment</button>
            </div>
          </form>
        </div>
      </div>
    @endif

    <script>
      // Modal trigger
      const openModalBtn = document.getElementById('openPaymentModal');
      openModalBtn?.addEventListener('click', () => {
        document.getElementById('paymentModal').classList.add('active');
        document.body.style.overflow = 'hidden';
      });

      ['closePaymentModal', 'cancelPaymentModal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', closePayment);
      });

      document.getElementById('paymentModal')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closePayment();
      });

      function closePayment() {
        document.getElementById('paymentModal').classList.remove('active');
        document.body.style.overflow = '';
      }

      // Dynamic action setup
      document.getElementById('invoice_id')?.addEventListener('change', function() {
        const invoiceId = this.value;
        const form = document.getElementById('paymentForm');
        if (invoiceId) {
          form.action = `/admin/invoices/${invoiceId}/payment`;
        } else {
          form.action = '';
        }

        const selectedOption = this.options[this.selectedIndex];
        const maxAmount = selectedOption.getAttribute('data-due');
        const amountInput = document.getElementById('amount');
        if (maxAmount) {
          amountInput.max = maxAmount;
          amountInput.placeholder = `Max: ₦${parseFloat(maxAmount).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
        } else {
          amountInput.removeAttribute('max');
          amountInput.placeholder = 'e.g. 50000';
        }
      });
    </script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
