<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dispatches Oversight - Country Yoghurt MD</title>
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
            <h2>Dispatches Oversight</h2>
            <p>Oversight panel — track product dispatch logs and delivery status across all distributors.</p>
          </div>
        </header>

        {{-- Filters --}}
        <section class="card inv-filter-bar">
          <form method="GET" action="{{ route('admin.dispatches.index') }}" class="inv-filters">
            <label class="search-wrap inv-search">
              <i class="bi bi-search search-icon"></i>
              <input type="search" name="search" placeholder="Search dispatch number…" value="{{ $search }}" />
            </label>

            <select name="distributor_id" class="filter-select" onchange="this.form.submit()">
              <option value="">All Distributors</option>
              @foreach ($distributors as $d)
                <option value="{{ $d->id }}" {{ $distributorId == $d->id ? 'selected' : '' }}>{{ $d->company_name ?: $d->name }}</option>
              @endforeach
            </select>

            <button type="submit" class="ghost-btn">Apply</button>
            @if ($search || $distributorId)
              <a href="{{ route('admin.dispatches.index') }}" class="ghost-btn">Clear</a>
            @endif
          </form>
          <span class="inv-count">{{ $dispatches->total() }} dispatch{{ $dispatches->total() !== 1 ? 'es' : '' }}</span>
        </section>

        {{-- Table --}}
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Dispatch Number</th>
                  <th>Distributor</th>
                  <th>Dispatched By</th>
                  <th>Date &amp; Time</th>
                  <th>Items Dispatched</th>
                  <th>Total Value (₦)</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($dispatches as $d)
                  <tr>
                    <td><strong style="color:#1d086c;">{{ $d->dispatch_number }}</strong></td>
                    <td>
                      <span class="inv-name">{{ $d->distributor->company_name ?: $d->distributor->name }}</span>
                      <small style="color:#666; display:block;">{{ $d->distributor->name }}</small>
                    </td>
                    <td><span style="font-size:0.9rem;">{{ $d->dispatcher->name }}</span></td>
                    <td>{{ $d->dispatched_at->format('d M Y, h:i A') }}</td>
                    <td>
                      <details style="cursor:pointer; font-size:0.9rem; color:#1d086c;">
                        <summary style="font-weight:500;">View ({{ $d->items->count() }}) item(s)</summary>
                        <div style="margin-top:6px; background:#f8f9fa; padding:8px; border-radius:6px; border:1px solid #eee; color:#333; line-height:1.4;">
                          @foreach ($d->items as $item)
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:0.85rem;">
                              <span>{{ $item->product->name }} (Qty: {{ $item->quantity }})</span>
                              <span style="font-weight:600;">@₦{{ number_format($item->unit_price, 2) }}</span>
                            </div>
                          @endforeach
                        </div>
                      </details>
                    </td>
                    <td><strong style="color:#2e7d32;">{{ number_format($d->total_amount, 2) }}</strong></td>
                    <td>
                      @if ($d->status === 'received')
                        <span class="status-badge badge-active"><i class="bi bi-check-circle-fill"></i> Received</span>
                      @else
                        <span class="status-badge badge-deleted" style="background:#e3f2fd; color:#1565c0; border:1px solid #bbdefb;"><i class="bi bi-truck"></i> Sent</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="empty-row">No dispatches found in the system.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if ($dispatches->hasPages())
            <div class="pagination-wrap">{{ $dispatches->links() }}</div>
          @endif
        </section>
      </main>
    </div>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
