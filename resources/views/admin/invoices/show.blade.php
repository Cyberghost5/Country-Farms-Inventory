<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice {{ $invoice->invoice_number }} - Country Yoghurt MD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <style>
      .details-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
      }
      .desktop-table-view table {
        min-width: auto !important;
      }
      .info-card {
        background: #ffffff;
        border: 1px solid #eef0f6;
        border-radius: 16px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.03);
        padding: 24px;
        transition: all 0.3s ease;
      }
      .info-card:hover {
        box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.05);
      }
      .info-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
      }
      .info-card-header h3 {
        color: #1d086c;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
      }
      .info-card-header i {
        font-size: 1.25rem;
        color: #6366f1;
      }
      .meta-list {
        list-style: none;
        padding: 0;
        margin: 0;
      }
      .meta-list li {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px dashed #e5e7eb;
        font-size: 0.9rem;
        gap: 12px;
      }
      .meta-list li:last-child {
        border-bottom: none;
      }
      .meta-list li .label {
        color: #6b7280;
        font-weight: 500;
        flex: 1;
        min-width: 100px;
      }
      .meta-list li .value {
        color: #111827;
        font-weight: 600;
        text-align: right;
        flex: 2;
        word-break: break-word;
      }
      
      .status-badge-lg {
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
      }
      .status-badge-lg.unpaid {
        background: #fdf2f2;
        color: #b71c1c;
        border: 1px solid #fde8e8;
      }
      .status-badge-lg.partially_paid {
        background: #e3f2fd;
        color: #1565c0;
        border: 1px solid #bbdefb;
      }
      .status-badge-lg.pending_approval {
        background: #fff8e1;
        color: #b45309;
        border: 1px solid #ffe082;
      }
      .status-badge-lg.paid {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #dcfce7;
      }

      .mobile-items-list {
        display: none;
        flex-direction: column;
        gap: 12px;
      }
      .mobile-item-card {
        background: #f9fafb;
        border: 1px solid #f3f4f6;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
      }
      .mobile-item-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
      }
      .mobile-item-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.95rem;
      }
      .mobile-item-sub {
        font-size: 0.8rem;
        color: #6b7280;
      }
      .mobile-item-pricing {
        text-align: right;
        display: flex;
        flex-direction: column;
        gap: 6px;
      }
      .mobile-item-subtotal {
        font-weight: 700;
        color: #1d086c;
        font-size: 1rem;
      }
      .mobile-item-qty {
        font-size: 0.8rem;
        background: #e0e7ff;
        color: #4338ca;
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 600;
        display: inline-block;
        width: fit-content;
        margin-left: auto;
      }
      .total-amount-box {
        background: #f5f4fd;
        border: 1px solid #d4cbf5;
        border-radius: 12px;
        padding: 20px;
        margin-top: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
      }
      .total-col {
        flex: 1;
        min-width: 120px;
      }
      .total-col.border-left {
        border-left: 1px solid #d4cbf5;
        padding-left: 16px;
      }

      @media (max-width: 900px) {
        .details-grid {
          grid-template-columns: 1fr;
          gap: 20px;
        }
      }
      @media (max-width: 600px) {
        .desktop-table-view {
          display: none;
        }
        .mobile-items-list {
          display: flex;
        }
        .total-col.border-left {
          border-left: none;
          padding-left: 0;
          border-top: 1px dashed #d4cbf5;
          padding-top: 12px;
        }
      }
    </style>
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">@include('partials._sidebar')</aside>

      <main class="main-content">
        <header class="topbar">
          <div class="title-block">
            <h2>Invoice Details</h2>
            <p>View invoice log breakdown, payment history, and balances.</p>
          </div>
          <div class="top-actions">
            @if ($user->isDistributor())
              <a href="{{ route('distributor.invoices.index') }}" class="ghost-btn" style="text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Back to Invoices
              </a>
            @else
              <a href="{{ route('admin.distributors.index') }}" class="ghost-btn" style="text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Back to Financials
              </a>
            @endif
          </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <div class="details-grid">
          {{-- Column 1: Items & Payments History --}}
          <div style="display:flex; flex-direction:column; gap:20px;">
            <section class="info-card">
              <div class="info-card-header">
                <i class="bi bi-basket3-fill" style="color: #1d086c;"></i>
                <h3>Invoice Items</h3>
              </div>

              {{-- Desktop Table View --}}
              <div class="table-scroll desktop-table-view">
                <table class="inv-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Quantity</th>
                      <th style="text-align:right;">Unit Price (₦)</th>
                      <th style="text-align:right;">Subtotal (₦)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if ($invoice->dispatch)
                      @foreach ($invoice->dispatch->items as $item)
                        <tr>
                          <td>
                            <span class="inv-name">{{ $item->product->name }}</span>
                            <span class="inv-notes">SKU: {{ $item->product->sku ?: '-' }} &middot; Vol: {{ $item->product->size_volume ?: '-' }}</span>
                          </td>
                          <td>{{ $item->quantity }}</td>
                          <td style="text-align:right;">₦{{ number_format($item->unit_price, 2) }}</td>
                          <td style="text-align:right;"><strong>₦{{ number_format($item->subtotal, 2) }}</strong></td>
                        </tr>
                      @endforeach
                    @else
                      <tr>
                        <td colspan="4" style="text-align: center; color:#999; padding: 20px;">No associated dispatch items.</td>
                      </tr>
                    @endif
                  </tbody>
                </table>
              </div>

              {{-- Mobile View Card List --}}
              <div class="mobile-items-list">
                @if ($invoice->dispatch)
                  @foreach ($invoice->dispatch->items as $item)
                    <div class="mobile-item-card">
                      <div class="mobile-item-info">
                        <span class="mobile-item-name">{{ $item->product->name }}</span>
                        <span class="mobile-item-sub">SKU: {{ $item->product->sku ?: '-' }}</span>
                        <span class="mobile-item-sub">Price: ₦{{ number_format($item->unit_price, 2) }}</span>
                      </div>
                      <div class="mobile-item-pricing">
                        <span class="mobile-item-subtotal">₦{{ number_format($item->subtotal, 2) }}</span>
                        <span class="mobile-item-qty">Qty: {{ $item->quantity }}</span>
                      </div>
                    </div>
                  @endforeach
                @else
                  <div style="text-align: center; color:#999; padding: 10px;">No associated dispatch items.</div>
                @endif
              </div>

              {{-- Summary box --}}
              <div class="total-amount-box">
                <div class="total-col" style="text-align: left;">
                  <span style="font-size:0.85rem; color:#6b7280; font-weight:500;">Total Invoice Value:</span>
                  <h3 style="color:#1d086c; margin: 4px 0 0; font-size:1.3rem; font-weight:700;">₦{{ number_format($invoice->total_amount, 2) }}</h3>
                </div>
                
                @php
                  $totalPaid = $invoice->payments()->where('status', 'approved')->sum('amount');
                @endphp
                <div class="total-col border-left" style="text-align: left;">
                  <span style="font-size:0.85rem; color:#6b7280; font-weight:500;">Total Paid:</span>
                  <h3 style="color:#2e7d32; margin: 4px 0 0; font-size:1.3rem; font-weight:700;">₦{{ number_format($totalPaid, 2) }}</h3>
                </div>

                <div class="total-col border-left" style="text-align: left;">
                  <span style="font-size:0.85rem; color:#6b7280; font-weight:500;">Balance Due:</span>
                  @if ($invoice->due_amount > 0)
                    <h3 style="color:#b71c1c; margin: 4px 0 0; font-size:1.3rem; font-weight:700;">₦{{ number_format($invoice->due_amount, 2) }}</h3>
                  @else
                    <h3 style="color:#2e7d32; margin: 4px 0 0; font-size:1.3rem; font-weight:700;">₦0.00</h3>
                  @endif
                </div>
              </div>
            </section>

            {{-- Payments History Section --}}
            <section class="info-card">
              <div class="info-card-header">
                <i class="bi bi-credit-card-2-front-fill" style="color: #1d086c;"></i>
                <h3>Payment History Logs</h3>
              </div>
              <div class="table-scroll">
                <table class="inv-table">
                  <thead>
                    <tr>
                      <th>Payment No.</th>
                      <th>Amount Paid (₦)</th>
                      <th>Payment Date</th>
                      <th>Method</th>
                      <th>Proof of Payment</th>
                      <th>Status</th>
                      <th>Recorded By</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($invoice->payments as $pay)
                      <tr>
                        <td><strong style="color:#1d086c;">{{ $pay->payment_number }}</strong></td>
                        <td><strong style="color:#2e7d32;">₦{{ number_format($pay->amount, 2) }}</strong></td>
                        <td>{{ $pay->payment_date->format('d M Y') }}</td>
                        <td><span class="cat-pill cat-others">{{ ucfirst(str_replace('_', ' ', $pay->payment_method)) }}</span></td>
                        <td>
                          @if ($pay->reference)
                            <a href="{{ asset('storage/' . $pay->reference) }}" target="_blank" class="ghost-btn btn-sm" style="color: var(--primary, #3a6b35); padding: 4px 10px; font-size: 0.72rem; text-decoration: none; display:inline-flex; align-items:center; gap:4px;">
                              <i class="bi bi-file-earmark-arrow-down"></i> View Proof
                            </a>
                          @else
                            -
                          @endif
                        </td>
                        <td>
                          @if ($pay->status === 'approved')
                            <span class="status-badge status-approved" style="background:#e8f5e9; color:#1a6b45; border:1px solid #c8e6c9; font-size:0.75rem; padding: 2px 8px;"><i class="bi bi-check-circle-fill"></i> Approved</span>
                          @elseif ($pay->status === 'pending')
                            <span class="status-badge status-pending" style="background:#fff8e1; color:#b45309; border:1px solid #ffe082; font-size:0.75rem; padding: 2px 8px;"><i class="bi bi-hourglass-split"></i> Pending</span>
                          @else
                            <span class="status-badge status-rejected" style="background:#fdecea; color:#c0392b; border:1px solid #ffcdd2; font-size:0.75rem; padding: 2px 8px;"><i class="bi bi-x-circle-fill"></i> Rejected</span>
                          @endif
                        </td>
                        <td>
                          <small style="color:#666; font-size:0.75rem;">
                            {{ $pay->recorder ? $pay->recorder->name : 'System' }}
                          </small>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="empty-row" style="text-align: center; color:#999; padding:20px;">No payments recorded against this invoice yet.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </section>
          </div>

          {{-- Column 2: Sidebar metadata & action panel --}}
          <div style="display:flex; flex-direction:column; gap:20px;">
            <section class="info-card">
              <div class="info-card-header">
                <i class="bi bi-info-circle-fill" style="color: #1d086c;"></i>
                <h3>Invoice Metadata</h3>
              </div>
              <ul class="meta-list">
                <li>
                  <span class="label">Invoice Number</span>
                  <span class="value" style="color:#1d086c; font-family:monospace; font-size:0.95rem;">{{ $invoice->invoice_number }}</span>
                </li>
                <li>
                  <span class="label">Date Created</span>
                  <span class="value">{{ $invoice->created_at->format('d M Y, h:i A') }}</span>
                </li>
                <li>
                  <span class="label">Due Date</span>
                  <span class="value">{{ $invoice->due_date->format('d M Y') }}</span>
                </li>
                <li>
                  <span class="label">Status</span>
                  <span class="value">
                    @if ($invoice->status === 'paid')
                      <span class="status-badge-lg paid"><i class="bi bi-check-circle-fill"></i> Paid</span>
                    @elseif ($invoice->status === 'partially_paid')
                      <span class="status-badge-lg partially_paid"><i class="bi bi-pie-chart-fill"></i> Partially Paid</span>
                    @elseif ($invoice->status === 'pending_approval')
                      <span class="status-badge-lg pending_approval"><i class="bi bi-hourglass-split"></i> Pending Approval</span>
                    @else
                      <span class="status-badge-lg unpaid"><i class="bi bi-exclamation-circle-fill"></i> Unpaid</span>
                    @endif
                  </span>
                </li>
              </ul>
            </section>

            <section class="info-card">
              <div class="info-card-header">
                <i class="bi bi-person-badge-fill" style="color: #1d086c;"></i>
                <h3>Distributor Info</h3>
              </div>
              <ul class="meta-list">
                <li>
                  <span class="label">Company Name</span>
                  <span class="value">{{ $invoice->distributor->company_name ?: '-' }}</span>
                </li>
                <li>
                  <span class="label">Contact Person</span>
                  <span class="value">{{ $invoice->distributor->name }}</span>
                </li>
                <li>
                  <span class="label">Phone</span>
                  <span class="value">{{ $invoice->distributor->phone }}</span>
                </li>
                <li>
                  <span class="label">Email</span>
                  <span class="value">{{ $invoice->distributor->email ?: '-' }}</span>
                </li>
                <li>
                  <span class="label">State / LGA</span>
                  <span class="value">
                    {{ $invoice->distributor->state ? $invoice->distributor->state . ($invoice->distributor->lga ? ' (' . $invoice->distributor->lga . ')' : '') : '-' }}
                  </span>
                </li>
                <li>
                  <span class="label">Address</span>
                  <span class="value">{{ $invoice->distributor->address ?: '-' }}</span>
                </li>
              </ul>
            </section>

            @if ($invoice->dispatch)
              <section class="info-card">
                <div class="info-card-header">
                  <i class="bi bi-truck-flatbed" style="color: #1d086c;"></i>
                  <h3>Related Dispatch</h3>
                </div>
                <ul class="meta-list">
                  <li>
                    <span class="label">Dispatch Number</span>
                    <span class="value" style="font-family: monospace;">{{ $invoice->dispatch->dispatch_number }}</span>
                  </li>
                  <li>
                    <span class="label">Dispatched Date</span>
                    <span class="value">{{ $invoice->dispatch->dispatched_at->format('d M Y, h:i A') }}</span>
                  </li>
                  <li>
                    <span class="label">Dispatched By</span>
                    <span class="value">{{ $invoice->dispatch->dispatcher->name }}</span>
                  </li>
                  @if ($invoice->dispatch->remarks)
                    <li>
                      <span class="label">Remarks</span>
                      <span class="value" style="font-size: 0.8rem; color: #555;">{{ $invoice->dispatch->remarks }}</span>
                    </li>
                  @endif
                </ul>
              </section>
            @endif

            {{-- Oversight Actions Panel (Record Payment) --}}
            @if ($user->isSuperAdmin() && $invoice->due_amount > 0)
              <section class="info-card" style="border-color:#d4cbf5; background:#f9f8fe;">
                <div class="info-card-header">
                  <i class="bi bi-credit-card-fill" style="color:#1d086c;"></i>
                  <h3 style="color:#1d086c;">Record Payment</h3>
                </div>
                <form method="POST" action="{{ route('admin.invoices.payment', $invoice->id) }}" class="inv-modal-form" enctype="multipart/form-data">
                  @csrf
                  <div class="form-group" style="margin-bottom:12px;">
                    <label for="amount" style="font-size:0.8rem; font-weight:600; color:#555;">Payment Amount (₦) *</label>
                    <input class="form-input" type="number" id="amount" name="amount" step="0.01" min="0.01" max="{{ $invoice->due_amount }}" required value="{{ $invoice->due_amount }}" style="padding: 8px 12px; font-size:0.9rem;" />
                  </div>

                  <div class="form-group" style="margin-bottom:12px;">
                    <label for="payment_date" style="font-size:0.8rem; font-weight:600; color:#555;">Payment Date *</label>
                    <input class="form-input" type="date" id="payment_date" name="payment_date" required value="{{ now()->toDateString() }}" style="padding: 8px 12px; font-size:0.9rem;" />
                  </div>

                  <div class="form-group" style="margin-bottom:12px;">
                    <label for="payment_method" style="font-size:0.8rem; font-weight:600; color:#555;">Payment Method *</label>
                    <select class="form-input" id="payment_method" name="payment_method" required style="padding: 8px 12px; font-size:0.9rem;">
                      <option value="bank_transfer">Bank Transfer</option>
                      <option value="cash">Cash</option>
                      <option value="cheque">Cheque</option>
                    </select>
                  </div>

                  <div class="form-group" style="margin-bottom:16px;">
                    <label for="upload_proof" style="font-size:0.8rem; font-weight:600; color:#555;">Proof of Payment (Image/PDF)</label>
                    <input class="form-input" type="file" id="upload_proof" name="proof_of_payment" accept="image/*,application/pdf" style="font-size:0.8rem;" />
                  </div>

                  <button type="submit" class="primary-btn" style="width:100%; background:#2e7d32; border-color:#2e7d32; justify-content:center; border-radius:10px; font-weight:600; padding:10px;">
                    <i class="bi bi-check-lg"></i> Record Payment
                  </button>
                </form>
              </section>
            @endif

            {{-- Distributor Actions Panel (Upload Payment Proof) --}}
            @if ($user->isDistributor() && $invoice->due_amount > 0)
              @if ($invoice->status === 'pending_approval')
                <section class="info-card" style="border-color:#ffe082; background:#fffdf6;">
                  <div class="info-card-header">
                    <i class="bi bi-clock-history" style="color:#b45309;"></i>
                    <h3 style="color:#b45309;">Verification Pending</h3>
                  </div>
                  <p style="font-size:0.8rem; color:#6b7280; margin: 0; line-height:1.5;">
                    Your payment proof has been uploaded and is currently awaiting administrator review and approval.
                  </p>
                </section>
              @else
                <section class="info-card" style="border-color:#c8e6c9; background:#f4faf4;">
                  <div class="info-card-header">
                    <i class="bi bi-cloud-arrow-up-fill" style="color:#2e7d32;"></i>
                    <h3 style="color:#2e7d32;">Submit Payment Proof</h3>
                  </div>
                  <form method="POST" action="{{ route('distributor.payments.upload') }}" class="inv-modal-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}" />
                    
                    <div class="form-group" style="margin-bottom:12px;">
                      <label for="upload_amount" style="font-size:0.8rem; font-weight:600; color:#555;">Amount Paid (₦) *</label>
                      <input class="form-input" type="number" id="upload_amount" name="amount" step="0.01" min="0.01" max="{{ $invoice->due_amount }}" required value="{{ $invoice->due_amount }}" style="padding: 8px 12px; font-size:0.9rem;" />
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                      <label for="upload_date" style="font-size:0.8rem; font-weight:600; color:#555;">Payment Date *</label>
                      <input class="form-input" type="date" id="upload_date" name="payment_date" required value="{{ now()->toDateString() }}" style="padding: 8px 12px; font-size:0.9rem;" />
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                      <label for="upload_method" style="font-size:0.8rem; font-weight:600; color:#555;">Payment Method *</label>
                      <select class="form-input" id="upload_method" name="payment_method" required style="padding: 8px 12px; font-size:0.9rem;">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                      </select>
                    </div>

                    <div class="form-group" style="margin-bottom:16px;">
                      <label for="upload_proof" style="font-size:0.8rem; font-weight:600; color:#555;">Proof of Payment (Image/PDF)</label>
                      <input class="form-input" type="file" id="upload_proof" name="proof_of_payment" accept="image/*,application/pdf" style="font-size:0.8rem;" />
                    </div>

                    <button type="submit" class="primary-btn" style="width:100%; background:#2e7d32; border-color:#2e7d32; justify-content:center; border-radius:10px; font-weight:600; padding:10px;">
                      <i class="bi bi-cloud-arrow-up"></i> Upload Payment Proof
                    </button>
                  </form>
                </section>
              @endif
            @endif
          </div>
        </div>
      </main>
    </div>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
