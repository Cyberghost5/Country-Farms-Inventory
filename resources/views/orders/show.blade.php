<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Details - Country Yoghurt MD</title>
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
      .status-badge-lg.pending {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fef3c7;
      }
      .status-badge-lg.approved {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #dcfce7;
      }
      .status-badge-lg.rejected {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fee2e2;
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
        text-align: right;
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
            <h2>Order details</h2>
            <p>View order summary, items, pricing, and history status.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('orders.index') }}" class="ghost-btn" style="text-decoration:none;">
              <i class="bi bi-arrow-left"></i> Back to List
            </a>
          </div>
        </header>

        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <div class="details-grid">
          {{-- Column 1: Items List --}}
          <div style="display:flex; flex-direction:column; gap:20px;">
            <section class="info-card">
              <div class="info-card-header">
                <i class="bi bi-basket3-fill"></i>
                <h3>Order Items</h3>
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
                    @foreach ($order->items as $item)
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
                  </tbody>
                </table>
              </div>

              {{-- Mobile View Card List --}}
              <div class="mobile-items-list">
                @foreach ($order->items as $item)
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
              </div>

              {{-- Total value box --}}
              <div class="total-amount-box">
                <span style="font-size:0.95rem; color:#6b7280; font-weight:500;">Total Order Value:</span>
                <h2 style="color:#1d086c; margin: 4px 0 0; font-size:1.6rem; font-weight:700;">₦{{ number_format($order->total_amount, 2) }}</h2>
              </div>
            </section>

            @if ($order->remarks)
              <section class="info-card">
                <div class="info-card-header">
                  <i class="bi bi-chat-text-fill"></i>
                  <h3>Distributor Remarks</h3>
                </div>
                <div style="background:#f9fafb; border:1px solid #f3f4f6; padding:16px; border-radius:12px; font-size:0.9rem; color:#374151; line-height:1.5; word-break:break-word;">
                  {{ $order->remarks }}
                </div>
              </section>
            @endif
          </div>

          {{-- Column 2: Order Metadata & Actions --}}
          <div style="display:flex; flex-direction:column; gap:20px;">
            <section class="info-card">
              <div class="info-card-header">
                <i class="bi bi-info-circle-fill"></i>
                <h3>Order Info</h3>
              </div>
              <ul class="meta-list">
                <li>
                  <span class="label">Order Number</span>
                  <span class="value" style="color:#1d086c; font-family:monospace; font-size:0.95rem;">{{ $order->order_number }}</span>
                </li>
                <li>
                  <span class="label">Date Placed</span>
                  <span class="value">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                </li>
                <li>
                  <span class="label">Status</span>
                  <span class="value">
                    @if ($order->status === 'pending')
                      <span class="status-badge-lg pending"><i class="bi bi-hourglass-split"></i> Pending</span>
                    @elseif ($order->status === 'approved')
                      <span class="status-badge-lg approved"><i class="bi bi-check-circle"></i> Approved</span>
                    @else
                      <span class="status-badge-lg rejected"><i class="bi bi-x-circle"></i> Rejected</span>
                    @endif
                  </span>
                </li>
                @if ($order->state)
                  <li>
                    <span class="label">Destination State</span>
                    <span class="value" style="color:#1d086c;">{{ $order->state }}</span>
                  </li>
                @endif
              </ul>

              @if ($order->status !== 'pending' && $order->processor)
                <div style="margin-top:18px; padding-top:18px; border-top:1px dashed #e5e7eb;">
                  <h4 style="color:#1d086c; font-size:0.85rem; font-weight:600; margin-bottom:12px; text-transform:uppercase; letter-spacing:0.05em;">Processor Details</h4>
                  <ul class="meta-list">
                    <li>
                      <span class="label">Processed By</span>
                      <span class="value">{{ $order->processor->name }}</span>
                    </li>
                    <li>
                      <span class="label">Processed At</span>
                      <span class="value">{{ $order->processed_at->format('d M Y, h:i A') }}</span>
                    </li>
                  </ul>
                </div>
              @endif
            </section>

            <section class="info-card">
              <div class="info-card-header">
                <i class="bi bi-person-badge-fill"></i>
                <h3>Distributor Info</h3>
              </div>
              <ul class="meta-list">
                <li>
                  <span class="label">Company Name</span>
                  <span class="value">{{ $order->distributor->company_name ?: '-' }}</span>
                </li>
                <li>
                  <span class="label">Contact Name</span>
                  <span class="value">{{ $order->distributor->name }}</span>
                </li>
                <li>
                  <span class="label">Phone</span>
                  <span class="value">{{ $order->distributor->phone }}</span>
                </li>
                <li>
                  <span class="label">Operating Location</span>
                  <span class="value">
                    {{ $order->distributor->state ? $order->distributor->state . ($order->distributor->lga ? ' (' . $order->distributor->lga . ')' : '') : '-' }}
                  </span>
                </li>
              </ul>
            </section>

            {{-- Oversight Actions: Approve / Reject (only Super Admin & GM for pending orders) --}}
            @if ($order->status === 'pending' && ($user->isSuperAdmin() || $user->isGeneralManager()))
              <section class="info-card" style="border-color:#d4cbf5; background:#f9f8fe;">
                <div class="info-card-header">
                  <i class="bi bi-shield-lock-fill" style="color:#1d086c;"></i>
                  <h3 style="color:#1d086c;">Oversight Actions</h3>
                </div>
                <p style="font-size:0.8rem; color:#6b7280; margin-bottom:16px; line-height:1.5;">
                  Please review stock levels before approving this order. Once approved, the distributor can receive invoice billing.
                </p>
                <div style="display:flex; flex-direction:column; gap:10px;">
                  <form method="POST" action="{{ route('orders.approve', $order->id) }}"
                        onsubmit="return confirm('Are you sure you want to approve Order #{{ $order->order_number }}?')">
                    @csrf
                    <button type="submit" class="primary-btn" style="width:100%; background:#2e7d32; border-color:#2e7d32; justify-content:center; border-radius:10px; font-weight:600; padding:10px;">
                      <i class="bi bi-check-lg"></i> Approve Order
                    </button>
                  </form>
                  <form method="POST" action="{{ route('orders.reject', $order->id) }}"
                        onsubmit="return confirm('Are you sure you want to reject Order #{{ $order->order_number }}?')">
                    @csrf
                    <button type="submit" class="danger-ghost" style="width:100%; justify-content:center; border-radius:10px; font-weight:600; padding:10px; border:1px solid #dc2626; color:#dc2626;">
                      <i class="bi bi-x-lg"></i> Reject Order
                    </button>
                  </form>
                </div>
              </section>
            @endif
          </div>
        </div>
      </main>
    </div>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
