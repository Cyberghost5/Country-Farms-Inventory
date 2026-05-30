<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payments Log - Country Yoghurt MD</title>
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
            <h2>Payments Log</h2>
            <p>Oversight panel - view all payments, filter by distributor or status, and approve/reject pending uploads.</p>
          </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- Filters --}}
        <section class="card inv-filter-bar" style="margin-bottom: 24px;">
          <form method="GET" action="{{ route('admin.payments.index') }}" class="inv-filters" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <select name="distributor_id" class="filter-select" onchange="this.form.submit()">
              <option value="">All Distributors</option>
              @foreach ($distributors as $d)
                <option value="{{ $d->id }}" {{ $distributorId == $d->id ? 'selected' : '' }}>
                  {{ $d->company_name ?: $d->name }}
                </option>
              @endforeach
            </select>

            <select name="status" class="filter-select" onchange="this.form.submit()">
              <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
              <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
              <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

            <button type="submit" class="ghost-btn">Apply</button>
            @if ($status !== 'all' || $distributorId)
              <a href="{{ route('admin.payments.index') }}" class="ghost-btn" style="text-decoration: none; line-height: 2;">Clear</a>
            @endif
          </form>
          <span class="inv-count">{{ $payments->total() }} payment{{ $payments->total() !== 1 ? 's' : '' }} found</span>
        </section>

        {{-- Payments Table --}}
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
                  <th>Status</th>
                  <th>Recorded By</th>
                  @if ($user->isSuperAdmin())
                    <th>Actions</th>
                  @endif
                </tr>
              </thead>
              <tbody>
                @forelse ($payments as $p)
                  <tr>
                    <td><strong style="color:#1d086c;">{{ $p->payment_number }}</strong></td>
                    <td>
                      <span class="inv-name">{{ $p->distributor->name }}</span>
                      @if ($p->distributor->company_name)
                        <small style="color:#666; display:block;">{{ $p->distributor->company_name }}</small>
                      @endif
                    </td>
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
                    @if ($user->isSuperAdmin())
                      <td>
                        @if ($p->status === 'pending')
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
                        @else
                          <span style="color:#aaa;">-</span>
                        @endif
                      </td>
                    @endif
                  </tr>
                @empty
                  <tr>
                    <td colspan="{{ $user->isSuperAdmin() ? 10 : 9 }}" class="empty-row">No payments recorded matching filters.</td>
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
