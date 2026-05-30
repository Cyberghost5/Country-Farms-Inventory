<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoices - Country Yoghurt MD</title>
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
            <h2>Invoices</h2>
            <p>Distributor panel - view invoices and outstanding financial balances.</p>
          </div>
        </header>

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
            <h4 class="stat-value" style="color:#b71c1c;">₦{{ number_format($outstandingBalance, 2) }}</h4>
            <small class="stat-label">Outstanding Balance</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#fff3cd;color:#856404;"><i class="bi bi-hourglass-split"></i></span></div>
            <h4 class="stat-value">{{ $invoices->whereIn('status', ['unpaid', 'partially_paid'])->count() }}</h4>
            <small class="stat-label">Unpaid Invoices</small>
          </article>
        </section>

        {{-- Filters --}}
        <section class="card inv-filter-bar">
          <form method="GET" action="{{ route('distributor.invoices.index') }}" class="inv-filters">
            <select name="status" class="filter-select" onchange="this.form.submit()">
              <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
              <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
              <option value="partially_paid" {{ $status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
              <option value="pending_approval" {{ $status === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
              <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
            <button type="submit" class="ghost-btn">Apply</button>
            @if ($status !== 'all')
              <a href="{{ route('distributor.invoices.index') }}" class="ghost-btn">Clear</a>
            @endif
          </form>
          <span class="inv-count">{{ $invoices->total() }} invoice{{ $invoices->total() !== 1 ? 's' : '' }}</span>
        </section>

        {{-- Table --}}
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Invoice Number</th>
                  <th>Dispatch Number</th>
                  <th>Created Date</th>
                  <th>Due Date</th>
                  <th>Total Value (₦)</th>
                  <th>Balance Due (₦)</th>
                  <th>Status</th>
                  <th>Items Details</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($invoices as $inv)
                  <tr>
                    <td><strong style="color:#1d086c;">{{ $inv->invoice_number }}</strong></td>
                    <td><code>{{ $inv->dispatch ? $inv->dispatch->dispatch_number : '-' }}</code></td>
                    <td>{{ $inv->created_at->format('d M Y') }}</td>
                    <td>
                      <span class="{{ now()->greaterThan($inv->due_date) && $inv->status !== 'paid' ? 'stock-badge-low' : '' }}">
                        {{ $inv->due_date->format('d M Y') }}
                      </span>
                    </td>
                    <td>{{ number_format($inv->total_amount, 2) }}</td>
                    <td>
                      @if ($inv->due_amount > 0)
                        <strong style="color:#b71c1c;">{{ number_format($inv->due_amount, 2) }}</strong>
                      @else
                        <span style="color:#2e7d32;">₦0.00</span>
                      @endif
                    </td>
                    <td>
                      @if ($inv->status === 'paid')
                        <span class="status-badge badge-active"><i class="bi bi-check-circle-fill"></i> Paid</span>
                      @elseif ($inv->status === 'partially_paid')
                        <span class="status-badge badge-inactive" style="background:#e3f2fd; color:#1565c0; border:1px solid #bbdefb;"><i class="bi bi-pie-chart-fill"></i> Partial</span>
                      @elseif ($inv->status === 'pending_approval')
                        <span class="status-badge status-pending" style="background:#fff8e1; color:#b45309; border:1px solid #ffe082;"><i class="bi bi-hourglass-split"></i> Pending Approval</span>
                      @else
                        <span class="status-badge badge-deleted" style="background:#fdecea; color:#b71c1c; border:1px solid #ffcdd2;"><i class="bi bi-exclamation-circle-fill"></i> Unpaid</span>
                      @endif
                    </td>
                    <td>
                      @if ($inv->dispatch)
                        <details style="cursor:pointer; font-size:0.9rem; color:#1d086c;">
                          <summary style="font-weight:500;">View items</summary>
                          <div style="margin-top:6px; background:#f8f9fa; padding:8px; border-radius:6px; border:1px solid #eee; color:#333; line-height:1.4;">
                            @foreach ($inv->dispatch->items as $item)
                              <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:0.85rem;">
                                <span>{{ $item->product->name }} (Qty: {{ $item->quantity }})</span>
                                <span style="font-weight:600;">@₦{{ number_format($item->unit_price, 2) }}</span>
                              </div>
                            @endforeach
                          </div>
                        </details>
                      @else
                        <span style="color:#aaa;">-</span>
                      @endif
                    </td>
                    <td>
                      @if ($inv->status !== 'paid' && $inv->status !== 'pending_approval')
                        <button class="primary-btn btn-sm open-upload-modal"
                                data-id="{{ $inv->id }}"
                                data-num="{{ $inv->invoice_number }}"
                                data-due="{{ $inv->due_amount }}"
                                style="padding: 4px 10px; font-size: 0.75rem; background: #2e7d32; border-color: #2e7d32;">
                          <i class="bi bi-cloud-arrow-up"></i> Pay
                        </button>
                      @else
                        <span style="color:#aaa;">-</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="empty-row">
                      <i class="bi bi-receipt" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#ccc;"></i>
                      No invoices found.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if ($invoices->hasPages())
            <div class="pagination-wrap">{{ $invoices->links() }}</div>
          @endif
        </section>
      </main>
    </div>
    {{-- ══════════ UPLOAD PAYMENT MODAL ══════════ --}}
    <div class="inv-modal-overlay" id="uploadPaymentModal">
      <div class="inv-modal">
        <div class="inv-modal-head">
          <h3>Upload Payment Proof</h3>
          <button class="inv-modal-close" id="closeUploadModal" type="button"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('distributor.payments.upload') }}" class="inv-modal-form" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="invoice_id" id="upload_invoice_id" />
          <div class="inv-modal-body">
            <div style="margin-bottom:16px; background:#f4f5f7; padding:12px; border-radius:8px; font-size:0.86rem; color:#444; border:1px solid #e2e8f0; line-height:1.6;">
              <div><strong>Invoice Number:</strong> <span id="upload_invoice_num_text" style="color:#1d086c; font-weight:600;"></span></div>
              <div style="margin-top:2px;"><strong>Balance Due:</strong> <span style="color:#b71c1c; font-weight:600;">₦<span id="upload_due_amount_text"></span></span></div>
            </div>

            <div class="form-group">
              <label for="upload_amount">Amount Paid (₦) *</label>
              <input class="form-input" type="number" id="upload_amount" name="amount" step="0.01" min="0.01" required placeholder="e.g. 50000" />
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label for="upload_date">Payment Date *</label>
                <input class="form-input" type="date" id="upload_date" name="payment_date" required value="{{ now()->toDateString() }}" />
              </div>
              <div class="form-group">
                <label for="upload_method">Payment Method *</label>
                <select class="form-input" id="upload_method" name="payment_method" required>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cash">Cash</option>
                  <option value="cheque">Cheque</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="upload_proof">Proof of Payment * (Image or PDF only)</label>
              <input class="form-input" type="file" id="upload_proof" name="proof_of_payment" accept="image/*,application/pdf" required />
            </div>
          </div>
          <div class="inv-modal-footer">
            <button type="button" class="ghost-btn" id="cancelUploadModal">Cancel</button>
            <button type="submit" class="primary-btn" style="background:#2e7d32; border-color:#2e7d32;"><i class="bi bi-cloud-arrow-up"></i> Upload Payment</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      // Handle upload payment modal
      const uploadModal = document.getElementById('uploadPaymentModal');
      const openModalButtons = document.querySelectorAll('.open-upload-modal');
      
      openModalButtons.forEach(btn => {
        btn.addEventListener('click', function() {
          const invoiceId = this.getAttribute('data-id');
          const invoiceNum = this.getAttribute('data-num');
          const dueAmount = this.getAttribute('data-due');

          document.getElementById('upload_invoice_id').value = invoiceId;
          document.getElementById('upload_invoice_num_text').textContent = invoiceNum;
          document.getElementById('upload_due_amount_text').textContent = parseFloat(dueAmount).toLocaleString('en-US', { minimumFractionDigits: 2 });
          
          const amountInput = document.getElementById('upload_amount');
          amountInput.max = dueAmount;
          amountInput.value = parseFloat(dueAmount).toFixed(2);
          amountInput.placeholder = `Max: ₦${parseFloat(dueAmount).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;

          uploadModal.classList.add('active');
          document.body.style.overflow = 'hidden';
        });
      });

      ['closeUploadModal', 'cancelUploadModal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', closeUploadModal);
      });

      uploadModal?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeUploadModal();
      });

      function closeUploadModal() {
        uploadModal.classList.remove('active');
        document.body.style.overflow = '';
      }
    </script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
