<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inventory Management - Country Yoghurt MD</title>
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
            <h2>Inventory Management</h2>
            <p>Store Manager panel - verify production batches and track product stock.</p>
          </div>
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
            <div class="stat-top"><span class="mini-icon"><i class="bi bi-layers"></i></span></div>
            <h4 class="stat-value">{{ number_format($stats['total_stock']) }}</h4>
            <small class="stat-label">Total Stock (Units)</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#fff3cd;color:#856404;"><i class="bi bi-hourglass-split"></i></span></div>
            <h4 class="stat-value">{{ $stats['pending_verification'] }}</h4>
            <small class="stat-label">Pending Verification</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#e3f2fd;color:#1565c0;"><i class="bi bi-truck"></i></span></div>
            <h4 class="stat-value">{{ $stats['dispatches'] }}</h4>
            <small class="stat-label">Dispatches</small>
          </article>
        </section>

        {{-- 1. PENDING VERIFICATION BATCHES --}}
        <h3 style="color:#1d086c; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-hourglass-split"></i> Pending Batches Verification
        </h3>
        <section class="card table-card" style="margin-bottom:30px;">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Batch Number</th>
                  <th>Quantity</th>
                  <th>Production Date</th>
                  <th>Expiry Date</th>
                  <th>Uploaded By</th>
                  <th>Remarks</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($pendingBatches as $batch)
                  <tr>
                    <td>
                      <span class="inv-name">{{ $batch->product->name }}</span>
                      <code class="sku-code">{{ $batch->product->sku }}</code>
                    </td>
                    <td><strong style="color:#1d086c;">{{ $batch->batch_number }}</strong></td>
                    <td>{{ number_format($batch->quantity) }}</td>
                    <td>{{ $batch->production_date->format('d M Y') }}</td>
                    <td>{{ $batch->expiry_date->format('d M Y') }}</td>
                    <td><span style="font-size:0.9rem;">{{ $batch->uploader->name }}</span></td>
                    <td>
                      @if ($batch->remarks)
                        <span class="inv-notes" title="{{ $batch->remarks }}">{{ Str::limit($batch->remarks, 30) }}</span>
                      @else
                        <span style="color:#aaa;">-</span>
                      @endif
                    </td>
                    <td>
                      <form method="POST" action="{{ route('store.inventory.verify', $batch->id) }}" 
                            onsubmit="return confirm('Verify Batch #{{ $batch->batch_number }} for {{ $batch->product->name }}? This adds {{ $batch->quantity }} units to verified inventory stock.')">
                        @csrf
                        <button type="submit" class="primary-btn btn-sm" style="background:#2e7d32; color:#fff; border-color:#2e7d32; padding: 4px 10px;">
                          <i class="bi bi-check-circle"></i> Verify
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="empty-row" style="padding: 24px;">
                      <i class="bi bi-check-circle-fill" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#2e7d32;"></i>
                      All production batch uploads verified! No pending reviews.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>

        {{-- 2. CURRENT INVENTORY STOCK LEVEL --}}
        <h3 style="color:#1d086c; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-layers"></i> Verified Stock Quantities
        </h3>
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>SKU</th>
                  <th>Category</th>
                  <th>Packaging & Size</th>
                  <th>Unit</th>
                  <th>Total Verified Stock</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($products as $product)
                  @php
                    $stock = $product->batches->sum('quantity');
                  @endphp
                  <tr>
                    <td><span class="inv-name">{{ $product->name }}</span></td>
                    <td><code class="sku-code">{{ $product->sku }}</code></td>
                    <td><span class="cat-pill cat-{{ $product->category }}">{{ ucfirst($product->category) }}</span></td>
                    <td>{{ $product->packaging_type ?: '-' }} &middot; {{ $product->size_volume ?: '-' }}</td>
                    <td>{{ ucfirst($product->unit) }}</td>
                    <td>
                      @if ($stock > 0)
                        <strong style="color:#2e7d32; font-size:1.1rem;">{{ number_format($stock) }}</strong>
                      @else
                        <span style="color:#888;">0</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="empty-row">
                      <i class="bi bi-layers-half" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#ccc;"></i>
                      No products found in system.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if ($products->hasPages())
            <div class="pagination-wrap">{{ $products->links() }}</div>
          @endif
        </section>
      </main>
    </div>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
