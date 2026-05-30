<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Orders - Country Yoghurt MD</title>
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
            <h2>{{ $user->isDistributor() ? 'My Orders' : 'Order Overview' }}</h2>
            <p>
              @if ($user->isDistributor())
                Place new orders and check status history.
              @else
                Oversight panel - view and approve/reject distributor orders.
              @endif
            </p>
          </div>
          @if ($user->isDistributor())
            <div class="top-actions">
              <a href="{{ route('orders.create') }}" class="primary-btn" style="text-decoration:none;">
                <i class="bi bi-cart-plus"></i> Place Order
              </a>
            </div>
          @endif
        </header>

        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- Status Filter Tabs --}}
        <section class="role-tabs" style="margin-bottom:16px;">
          @php 
            $statuses = ['all' => 'All Orders', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];
          @endphp
          @foreach ($statuses as $key => $label)
            <a href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => $key])) }}"
               class="role-tab {{ $status === $key ? 'active' : '' }}">
              {{ $label }}
            </a>
          @endforeach
        </section>

        {{-- Search filter --}}
        <section class="card inv-filter-bar" style="margin-bottom:16px;">
          <form method="GET" action="{{ route('orders.index') }}" class="inv-filters">
            <input type="hidden" name="status" value="{{ $status }}" />
            <label class="search-wrap inv-search">
              <i class="bi bi-search search-icon"></i>
              <input type="search" name="search" placeholder="Search order number or name…" value="{{ $search }}" />
            </label>
            <button type="submit" class="ghost-btn">Apply</button>
            @if ($search)
              <a href="{{ route('orders.index', ['status' => $status]) }}" class="ghost-btn">Clear</a>
            @endif
          </form>
          <span class="inv-count">{{ $orders->total() }} order{{ $orders->total() !== 1 ? 's' : '' }}</span>
        </section>

        {{-- Orders table --}}
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Order Number</th>
                  @if (!$user->isDistributor())
                    <th>Distributor</th>
                  @endif
                  <th>Total Amount (₦)</th>
                  <th>Status</th>
                  <th>Date Placed</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($orders as $o)
                  <tr>
                    <td>
                      <strong style="color:#1d086c;">{{ $o->order_number }}</strong>
                    </td>
                    @if (!$user->isDistributor())
                      <td>
                        <span class="inv-name">{{ $o->distributor->company_name ?: $o->distributor->name }}</span>
                        <span class="inv-notes">{{ $o->distributor->name }}</span>
                      </td>
                    @endif
                    <td>
                      <strong>₦{{ number_format($o->total_amount, 2) }}</strong>
                    </td>
                    <td>
                      @if ($o->status === 'pending')
                        <span class="status-badge status-pending"><i class="bi bi-hourglass-split"></i> Pending</span>
                      @elseif ($o->status === 'approved')
                        <span class="status-badge status-approved"><i class="bi bi-check-circle"></i> Approved</span>
                      @else
                        <span class="status-badge status-rejected"><i class="bi bi-x-circle"></i> Rejected</span>
                      @endif
                    </td>
                    <td>
                      {{ $o->created_at->format('d M Y, h:i A') }}
                    </td>
                    <td>
                      <div class="inv-actions">
                        <a href="{{ route('orders.show', $o->id) }}" class="ghost-btn btn-sm" title="View Details">
                          <i class="bi bi-eye"></i> View
                        </a>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="{{ $user->isDistributor() ? 5 : 6 }}" class="empty-row">
                      <i class="bi bi-cart" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#ccc;"></i>
                      No orders found.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if ($orders->hasPages())
            <div class="pagination-wrap">{{ $orders->links() }}</div>
          @endif
        </section>
      </main>
    </div>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
