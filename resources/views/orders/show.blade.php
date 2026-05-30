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
        gap: 20px;
        margin-bottom: 20px;
      }
      .meta-list {
        list-style: none;
        padding: 0;
        margin: 0;
      }
      .meta-list li {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0ebe0;
        font-size: 0.88rem;
      }
      .meta-list li:last-child {
        border-bottom: none;
      }
      .meta-list li strong {
        color: #1d086c;
      }
      @media (max-width: 900px) {
        .details-grid {
          grid-template-columns: 1fr;
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
            <h2>Order Details</h2>
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
            <section class="card" style="padding:24px;">
              <h3 style="color:#1d086c; margin-bottom:16px; border-bottom:1px solid #f0ebe0; padding-bottom:8px;">Order Items</h3>
              <div class="table-scroll">
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
                  <tfoot>
                    <tr>
                      <td colspan="3" style="text-align:right; font-weight:600; padding:16px;">Total Value:</td>
                      <td style="text-align:right; font-weight:700; font-size:1.1rem; color:#1d086c; padding:16px;">
                        ₦{{ number_format($order->total_amount, 2) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </section>

            @if ($order->remarks)
              <section class="card" style="padding:24px;">
                <h3 style="color:#1d086c; margin-bottom:12px;">Distributor Remarks</h3>
                <div style="background:#fefdfb; border:1px solid #f0ebe0; padding:14px; border-radius:8px; font-size:0.88rem; color:#333; line-height:1.5;">
                  {{ $order->remarks }}
                </div>
              </section>
            @endif
          </div>

          {{-- Column 2: Order Metadata & Actions --}}
          <div style="display:flex; flex-direction:column; gap:20px;">
            <section class="card" style="padding:24px;">
              <h3 style="color:#1d086c; margin-bottom:16px; border-bottom:1px solid #f0ebe0; padding-bottom:8px;">Order Info</h3>
              <ul class="meta-list">
                <li>
                  <span>Order Number</span>
                  <strong>{{ $order->order_number }}</strong>
                </li>
                <li>
                  <span>Date Placed</span>
                  <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
                </li>
                <li>
                  <span>Status</span>
                  <span>
                    @if ($order->status === 'pending')
                      <span class="status-badge status-pending"><i class="bi bi-hourglass-split"></i> Pending</span>
                    @elseif ($order->status === 'approved')
                      <span class="status-badge status-approved"><i class="bi bi-check-circle"></i> Approved</span>
                    @else
                      <span class="status-badge status-rejected"><i class="bi bi-x-circle"></i> Rejected</span>
                    @endif
                  </span>
                </li>
              </ul>

              @if ($order->status !== 'pending' && $order->processor)
                <div style="margin-top:16px; padding-top:16px; border-top:1px dashed #f0ebe0;">
                  <h4 style="color:#1d086c; font-size:0.85rem; margin-bottom:8px;">Processor Details</h4>
                  <ul class="meta-list">
                    <li>
                      <span>Processed By</span>
                      <strong>{{ $order->processor->name }}</strong>
                    </li>
                    <li>
                      <span>Processed At</span>
                      <span>{{ $order->processed_at->format('d M Y, h:i A') }}</span>
                    </li>
                  </ul>
                </div>
              @endif
            </section>

            <section class="card" style="padding:24px;">
              <h3 style="color:#1d086c; margin-bottom:16px; border-bottom:1px solid #f0ebe0; padding-bottom:8px;">Distributor Info</h3>
              <ul class="meta-list">
                <li>
                  <span>Company Name</span>
                  <strong>{{ $order->distributor->company_name ?: '-' }}</strong>
                </li>
                <li>
                  <span>Contact Name</span>
                  <span>{{ $order->distributor->name }}</span>
                </li>
                <li>
                  <span>Phone</span>
                  <span>{{ $order->distributor->phone }}</span>
                </li>
                <li>
                  <span>Location</span>
                  <span>
                    {{ $order->distributor->state ? $order->distributor->state . ($order->distributor->lga ? ' (' . $order->distributor->lga . ')' : '') : '-' }}
                  </span>
                </li>
              </ul>
            </section>

            {{-- Oversight Actions: Approve / Reject (only Super Admin & GM for pending orders) --}}
            @if ($order->status === 'pending' && ($user->isSuperAdmin() || $user->isGeneralManager()))
              <section class="card" style="padding:24px; border-color:#d4cbf5; background:#f9f8fe;">
                <h3 style="color:#1d086c; margin-bottom:14px;">Actions</h3>
                <p style="font-size:0.8rem; color:#666; margin-bottom:16px; line-height:1.4;">
                  Please review stock levels before approving this order. Once approved, the distributor can receive invoice billing.
                </p>
                <div style="display:flex; flex-direction:column; gap:10px;">
                  <form method="POST" action="{{ route('orders.approve', $order->id) }}"
                        onsubmit="return confirm('Are you sure you want to approve Order #{{ $order->order_number }}?')">
                    @csrf
                    <button type="submit" class="primary-btn" style="width:100%; background:#2e7d32; border-color:#2e7d32; justify-content:center;">
                      <i class="bi bi-check-lg"></i> Approve Order
                    </button>
                  </form>
                  <form method="POST" action="{{ route('orders.reject', $order->id) }}"
                        onsubmit="return confirm('Are you sure you want to reject Order #{{ $order->order_number }}?')">
                    @csrf
                    <button type="submit" class="danger-ghost" style="width:100%; justify-content:center; border-radius:10px; font-weight:600;">
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
