<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reports &amp; Analytics - Country Yoghurt MD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      .reports-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
      }
      .chart-container {
        position: relative;
        height: 280px;
        width: 100%;
      }
      .financial-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
      }
      @media (max-width: 1024px) {
        .reports-grid {
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
            <h2>Reports &amp; Analytics</h2>
            <p>Oversight panel — monitor system-wide KPIs, sales analytics, and audit log history.</p>
          </div>
        </header>

        {{-- KPI bar --}}
        <section class="kpi-grid" style="margin-bottom:24px;">
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon"><i class="bi bi-layers"></i></span></div>
            <h4 class="stat-value">{{ number_format($stats['total_stock']) }}</h4>
            <small class="stat-label">Total Verified Stock (Units)</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#e3f2fd;color:#1565c0;"><i class="bi bi-box-seam"></i></span></div>
            <h4 class="stat-value">{{ $stats['active_products'] }}</h4>
            <small class="stat-label">Active Products</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="bi bi-currency-dollar"></i></span></div>
            <h4 class="stat-value" style="color:#2e7d32;">₦{{ number_format($stats['total_revenue'], 2) }}</h4>
            <small class="stat-label">Revenue Collected</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#fdecea;color:#b71c1c;"><i class="bi bi-wallet2"></i></span></div>
            <h4 class="stat-value" style="color:#b71c1c;">₦{{ number_format($stats['total_outstanding'], 2) }}</h4>
            <small class="stat-label">Outstanding Receivables</small>
          </article>
        </section>

        {{-- Charts Grid --}}
        <section class="reports-grid">
          <div class="card" style="padding:20px;">
            <h3 style="color:#1d086c; margin-top:0; margin-bottom:16px;">Stock Levels by Product</h3>
            <div class="chart-container">
              <canvas id="stockChart"></canvas>
            </div>
          </div>
          <div class="financial-grid">
            <div class="card" style="padding:20px;">
              <h3 style="color:#1d086c; margin-top:0; margin-bottom:16px;">Financial Summary</h3>
              <div class="chart-container" style="height:210px;">
                <canvas id="financialChart"></canvas>
              </div>
            </div>
          </div>
        </section>

        {{-- Audit Trail Log --}}
        <h3 style="color:#1d086c; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-shield-check"></i> Deleted Inventory Log &amp; Audit Trail
        </h3>
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Timestamp</th>
                  <th>User Name</th>
                  <th>Action</th>
                  <th>Deleted Item Details</th>
                  <th>Reason for Deletion</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($auditLogs as $log)
                  <tr>
                    <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                    <td>
                      <span class="inv-name">{{ $log->user_name }}</span>
                      <small style="color:#888;">ID: {{ $log->user_id ?: 'Deleted User' }}</small>
                    </td>
                    <td>
                      @if ($log->action === 'delete_batch')
                        <span class="status-badge badge-deleted" style="background:#fdecea; color:#b71c1c; border:1px solid #ffcdd2;">Deleted Batch</span>
                      @else
                        <span class="status-badge badge-inactive">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                      @endif
                    </td>
                    <td>
                      @if (is_array($log->item_details))
                        <div style="font-size:0.85rem; line-height:1.4; color:#333;">
                          <div><strong>Batch:</strong> <code style="color:#1d086c;">{{ $log->item_details['batch_number'] ?? '—' }}</code></div>
                          <div><strong>Product:</strong> {{ $log->item_details['product_name'] ?? '—' }}</div>
                          <div><strong>Qty:</strong> {{ number_format($log->item_details['quantity'] ?? 0) }}</div>
                        </div>
                      @else
                        <span style="color:#aaa;">—</span>
                      @endif
                    </td>
                    <td>
                      <p style="margin:0; font-size:0.9rem; font-style:italic; color:#555;">
                        "{{ $log->reason }}"
                      </p>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="empty-row">
                      <i class="bi bi-journal-text" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#ccc;"></i>
                      No actions logged in the audit trail.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if ($auditLogs->hasPages())
            <div class="pagination-wrap">{{ $auditLogs->links() }}</div>
          @endif
        </section>
      </main>
    </div>

    {{-- Chart configurations --}}
    <script>
      // 1. Stock levels chart (Bar chart)
      const productStockData = @json($productStockData);
      const stockLabels = productStockData.map(d => d.name);
      const stockValues = productStockData.map(d => d.stock);

      const ctxStock = document.getElementById('stockChart').getContext('2d');
      new Chart(ctxStock, {
        type: 'bar',
        data: {
          labels: stockLabels,
          datasets: [{
            label: 'Stock Quantity (Units)',
            data: stockValues,
            backgroundColor: '#1d086c',
            borderWidth: 0,
            borderRadius: 4,
            barThickness: 24,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: '#f0f2f5' }
            },
            x: {
              grid: { display: false }
            }
          }
        }
      });

      // 2. Financial Overview Chart (Pie Chart)
      const totalRevenue = @json($stats['total_revenue']);
      const totalOutstanding = @json($stats['total_outstanding']);

      const ctxFin = document.getElementById('financialChart').getContext('2d');
      new Chart(ctxFin, {
        type: 'pie',
        data: {
          labels: ['Collected Revenue (₦)', 'Outstanding Balance (₦)'],
          datasets: [{
            data: [totalRevenue, totalOutstanding],
            backgroundColor: ['#2e7d32', '#b71c1c'],
            borderWidth: 1,
            borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: { boxWidth: 12, padding: 12 }
            }
          }
        }
      });
    </script>

    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
