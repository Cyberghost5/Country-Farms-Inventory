<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - Country Yoghurt MD</title>
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
      <aside class="sidebar" id="sidebar">
        @include('partials._sidebar')
      </aside>

      <main class="main-content">

        {{-- Page header --}}
        <header class="dash-header">
          <div>
            <h2 class="dash-title">Dashboard</h2>
            <p class="dash-sub">{{ now()->format('l, d F Y') }} &middot; {{ $user->role_label }}</p>
          </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
          <div class="lp-success" style="margin:0 0 20px;">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
          </div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin:0 0 20px;">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
          </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════
             SUPER ADMIN & GENERAL MANAGER DASHBOARD
        ════════════════════════════════════════════════════════════ --}}
        @if ($user->isOversight())

          <section class="kpi-grid" style="margin-bottom:24px;">
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="bi bi-cash-coin"></i></span>
                <span class="stat-label" style="color:#2e7d32; font-weight:600;">Total Revenue Collected</span>
              </div>
              <h4 class="stat-value" style="color:#2e7d32;">₦{{ number_format($stats['total_revenue'] ?? 0, 2) }}</h4>
              <p class="stat-foot">From approved payments</p>
            </div>

            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#fdecea;color:#b71c1c;"><i class="bi bi-wallet2"></i></span>
                <span class="stat-label" style="color:#b71c1c; font-weight:600;">Total Outstanding</span>
              </div>
              <h4 class="stat-value" style="color:#b71c1c;">₦{{ number_format($stats['total_outstanding'] ?? 0, 2) }}</h4>
              <p class="stat-foot">Unpaid &amp; pending approvals</p>
            </div>

            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#e3f2fd;color:#1565c0;"><i class="bi bi-layers"></i></span>
                <span class="stat-label" style="color:#1565c0; font-weight:600;">Total Stock in Store</span>
              </div>
              <h4 class="stat-value" style="color:#1565c0;">{{ number_format($stats['total_stock'] ?? 0) }}</h4>
              <p class="stat-foot">Units of verified inventory</p>
            </div>

            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#f3e5f5;color:#7b1fa2;"><i class="bi bi-shop"></i></span>
                <span class="stat-label" style="color:#7b1fa2; font-weight:600;">Active Distributors</span>
              </div>
              <h4 class="stat-value" style="color:#7b1fa2;">{{ $stats['distributors'] ?? 0 }}</h4>
              <p class="stat-foot">Registered distributor accounts</p>
            </div>
          </section>

          {{-- Module quick-links --}}
          <section class="dash-coming-soon-grid">
            <a href="{{ route('admin.products.index') }}" class="card coming-card" style="text-decoration:none;color:inherit;">
              <div class="coming-icon"><i class="bi bi-box-seam"></i></div>
              <div>
                <h3>Products</h3>
                <p>{{ $stats['total_products'] ?? 0 }} products · {{ $stats['active_products'] ?? 0 }} active</p>
              </div>
            </a>
            <a href="{{ route('admin.users.index') }}" class="card coming-card" style="text-decoration:none;color:inherit;">
              <div class="coming-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="bi bi-people"></i></div>
              <div>
                <h3>Users</h3>
                <p>Manage staff and distributors</p>
              </div>
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="card coming-card" style="text-decoration:none;color:inherit;">
              <div class="coming-icon" style="background:#fff3e0;color:#e65100;"><i class="bi bi-tags"></i></div>
              <div>
                <h3>Pricing</h3>
                <p>Distributor prices and discounts</p>
              </div>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="card coming-card" style="text-decoration:none;color:inherit;">
              <div class="coming-icon" style="background:#e0f2fe;color:#0369a1;"><i class="bi bi-bar-chart-line"></i></div>
              <div>
                <h3>Reports</h3>
                <p>Analytics &amp; audit logs</p>
              </div>
            </a>
          </section>

        {{-- ═══════════════════════════════════════════════════════════
             PRODUCTION MANAGER DASHBOARD
        ════════════════════════════════════════════════════════════ --}}
        @elseif ($user->isProductionManager())

          <section class="kpi-grid" style="margin-bottom:24px;">
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-clipboard-plus"></i></span>
                <span class="stat-label">My Uploads</span>
              </div>
              <h4 class="stat-value">{{ $stats['total_uploads'] ?? 0 }}</h4>
              <p class="stat-foot">Inventory batches uploaded</p>
            </div>
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#fff3cd;color:#856404;"><i class="bi bi-hourglass-split"></i></span>
                <span class="stat-label">Pending Verification</span>
              </div>
              <h4 class="stat-value">{{ $stats['pending_verification'] ?? 0 }}</h4>
              <p class="stat-foot">Awaiting store manager review</p>
            </div>
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#d1e7dd;color:#0a3622;"><i class="bi bi-check-circle"></i></span>
                <span class="stat-label">Verified</span>
              </div>
              <h4 class="stat-value">{{ $stats['verified'] ?? 0 }}</h4>
              <p class="stat-foot">Confirmed batches</p>
            </div>
          </section>

          <div class="card" style="padding:32px;text-align:center;">
            <div style="font-size:2.5rem;margin-bottom:12px;color:#1d086c;"><i class="bi bi-clipboard-plus"></i></div>
            <h3 style="margin:0 0 8px;color:#1d086c;">Ready to Upload Inventory?</h3>
            <p style="color:#6c757d;margin-bottom:20px;">Upload and track inventory batches directly.</p>
            <a href="{{ route('production.batches.index') }}" class="primary-btn" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
              <i class="bi bi-plus-lg"></i> Manage Batches
            </a>
          </div>

        {{-- ═══════════════════════════════════════════════════════════
             STORE MANAGER DASHBOARD
        ════════════════════════════════════════════════════════════ --}}
        @elseif ($user->isStoreManager())

          <section class="kpi-grid" style="margin-bottom:24px;">
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-layers"></i></span>
                <span class="stat-label">Total Stock</span>
              </div>
              <h4 class="stat-value">{{ number_format($stats['total_stock'] ?? 0) }}</h4>
              <p class="stat-foot">Units in verified inventory</p>
            </div>
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#fff3cd;color:#856404;"><i class="bi bi-hourglass-split"></i></span>
                <span class="stat-label">Pending Verification</span>
              </div>
              <h4 class="stat-value">{{ $stats['pending_verification'] ?? 0 }}</h4>
              <p class="stat-foot">Batches awaiting your review</p>
            </div>
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#e3f2fd;color:#1565c0;"><i class="bi bi-truck"></i></span>
                <span class="stat-label">Dispatches</span>
              </div>
              <h4 class="stat-value">{{ $stats['dispatches'] ?? 0 }}</h4>
              <p class="stat-foot">Products dispatched</p>
            </div>
          </section>

          <div class="card" style="padding:32px;text-align:center;">
            <div style="font-size:2.5rem;margin-bottom:12px;color:#1d086c;"><i class="bi bi-layers"></i></div>
            <h3 style="margin:0 0 8px;color:#1d086c;">Verification & Dispatch</h3>
            <p style="color:#6c757d;margin-bottom:20px;">Verify pending production batches and dispatch products to distributors.</p>
            <div style="display:flex;justify-content:center;gap:12px;">
              <a href="{{ route('store.inventory.index') }}" class="primary-btn" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                <i class="bi bi-layers"></i> Verify Stock
              </a>
              <a href="{{ route('store.dispatches.index') }}" class="ghost-btn" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                <i class="bi bi-truck"></i> Manage Dispatches
              </a>
            </div>
          </div>

        {{-- ═══════════════════════════════════════════════════════════
             DISTRIBUTOR DASHBOARD
        ════════════════════════════════════════════════════════════ --}}
        @elseif ($user->isDistributor())

          <section class="kpi-grid" style="margin-bottom:24px;">
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-box-arrow-in-down"></i></span>
                <span class="stat-label">Products Received</span>
              </div>
              <h4 class="stat-value">{{ number_format($stats['received_products'] ?? 0) }}</h4>
              <p class="stat-foot">Total units received</p>
            </div>
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#fff3cd;color:#856404;"><i class="bi bi-receipt"></i></span>
                <span class="stat-label">Invoices</span>
              </div>
              <h4 class="stat-value">{{ $stats['invoices_count'] ?? 0 }}</h4>
              <p class="stat-foot">Outstanding invoices</p>
            </div>
            <div class="stat-card">
              <div class="stat-top">
                <span class="mini-icon" style="background:#fdecea;color:#b71c1c;"><i class="bi bi-exclamation-circle"></i></span>
                <span class="stat-label">Outstanding Balance</span>
              </div>
              <h4 class="stat-value">₦{{ number_format($stats['outstanding_balance'] ?? 0, 2) }}</h4>
              <p class="stat-foot">Amount due</p>
            </div>
          </section>

          <div class="card" style="padding:32px;text-align:center;">
            <div style="font-size:2.5rem;margin-bottom:12px;color:#1d086c;"><i class="bi bi-shop"></i></div>
            <h3 style="margin:0 0 8px;color:#1d086c;">Welcome, {{ $user->company_name ?: $user->name }}</h3>
            <p style="color:#6c757d;margin-bottom:20px;">Track your received product shipments, outstanding invoices, and payments.</p>
            <div style="display:flex;justify-content:center;gap:12px;">
              <a href="{{ route('distributor.received.index') }}" class="primary-btn" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                <i class="bi bi-box-arrow-in-down"></i> Received Shipments
              </a>
              <a href="{{ route('distributor.invoices.index') }}" class="ghost-btn" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                <i class="bi bi-receipt"></i> View Invoices
              </a>
            </div>
          </div>

        @endif

      </main>
    </div>

    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
