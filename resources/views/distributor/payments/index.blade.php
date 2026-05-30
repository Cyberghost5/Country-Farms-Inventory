<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment History - Country Yoghurt MD</title>
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
            <h2>Payment History</h2>
            <p>Distributor panel - view payments made against your invoices.</p>
          </div>
        </header>

        {{-- Table --}}
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Payment Number</th>
                  <th>Invoice Number</th>
                  <th>Amount Paid (₦)</th>
                  <th>Payment Date</th>
                  <th>Payment Method</th>
                  <th>Proof of Payment</th>
                  <th>Status</th>
                  <th>Recorded By</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($payments as $p)
                  <tr>
                    <td><strong style="color:#1d086c;">{{ $p->payment_number }}</strong></td>
                    <td><code>{{ $p->invoice->invoice_number }}</code></td>
                    <td><strong style="color:#2e7d32;">{{ number_format($p->amount, 2) }}</strong></td>
                    <td>{{ $p->payment_date->format('d M Y') }}</td>
                    <td><span class="cat-pill cat-others">{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</span></td>
                    <td>
                      @if ($p->reference)
                        <a href="{{ asset('storage/' . $p->reference) }}" target="_blank" class="link-btn" style="text-decoration: none;">
                          <i class="bi bi-file-earmark-arrow-down"></i> View Proof
                        </a>
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      @if ($p->status === 'approved')
                        <span class="status-badge status-approved" style="background:#e8f5e9; color:#1a6b45; border:1px solid #c8e6c9;"><i class="bi bi-check-circle-fill"></i> Approved</span>
                      @elseif ($p->status === 'pending')
                        <span class="status-badge status-pending" style="background:#fff8e1; color:#b45309; border:1px solid #ffe082;"><i class="bi bi-hourglass-split"></i> Pending</span>
                      @else
                        <span class="status-badge status-rejected" style="background:#fdecea; color:#c0392b; border:1px solid #ffcdd2;"><i class="bi bi-x-circle-fill"></i> Rejected</span>
                      @endif
                    </td>
                    <td>
                      <small style="color:#666;">
                        {{ $p->recorder->name }}
                        @if ($p->recorder->role === 'distributor')
                          (Distributor)
                        @else
                          (Admin)
                        @endif
                      </small>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="empty-row">
                      <i class="bi bi-credit-card" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#ccc;"></i>
                      No payments recorded yet.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if ($payments->hasPages())
            <div class="pagination-wrap">{{ $payments->links() }}</div>
          @endif
        </section>
      </main>
    </div>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
