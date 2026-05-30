<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inventory Oversight - Country Yoghurt MD</title>
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
            <h2>Inventory Oversight</h2>
            <p>Oversight panel - monitor system-wide stock levels and batch history.</p>
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
            <div class="stat-top"><span class="mini-icon" style="background:#fff3cd;color:#856404;"><i class="bi bi-hourglass-split"></i></span></div>
            <h4 class="stat-value">{{ $stats['pending_batches'] }}</h4>
            <small class="stat-label">Pending Batches</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#d1e7dd;color:#0a3622;"><i class="bi bi-check-circle"></i></span></div>
            <h4 class="stat-value">{{ $stats['verified_batches'] }}</h4>
            <small class="stat-label">Verified Batches</small>
          </article>
        </section>

        {{-- 1. VERIFIED STOCK QUANTITIES SUMMARY --}}
        <h3 style="color:#1d086c; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-grid-3x3-gap"></i> Stock Levels Summary
        </h3>
        <section class="card table-card" style="margin-bottom:30px;">
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
                @forelse ($stockProducts as $product)
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
                    <td colspan="6" class="empty-row">No products registered.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>

        {{-- 2. ALL PRODUCTION BATCHES LOG --}}
        <h3 style="color:#1d086c; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-clock-history"></i> Production Batches Log
        </h3>
        
        {{-- Filters --}}
        <section class="card inv-filter-bar">
          <form method="GET" action="{{ route('admin.inventory.index') }}" class="inv-filters">
            <label class="search-wrap inv-search">
              <i class="bi bi-search search-icon"></i>
              <input type="search" name="search" placeholder="Search batch number…" value="{{ $search }}" />
            </label>
            
            <select name="status" class="filter-select" onchange="this.form.submit()">
              <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
              <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="verified" {{ $status === 'verified' ? 'selected' : '' }}>Verified</option>
            </select>

            <select name="product_id" class="filter-select" onchange="this.form.submit()">
              <option value="">All Products</option>
              @foreach ($filterProducts as $fp)
                <option value="{{ $fp->id }}" {{ $productId == $fp->id ? 'selected' : '' }}>{{ $fp->name }}</option>
              @endforeach
            </select>

            <button type="submit" class="ghost-btn">Apply</button>
            @if ($search || $status !== 'all' || $productId)
              <a href="{{ route('admin.inventory.index') }}" class="ghost-btn">Clear</a>
            @endif
          </form>
          <span class="inv-count">{{ $batches->total() }} batch{{ $batches->total() !== 1 ? 'es' : '' }}</span>
        </section>

        <section class="card table-card">
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
                  <th>Status</th>
                  <th>Verification Details</th>
                  @if ($user->isSuperAdmin())
                    <th>Actions</th>
                  @endif
                </tr>
              </thead>
              <tbody>
                @forelse ($batches as $batch)
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
                      @if ($batch->is_verified)
                        <span class="status-badge badge-active"><i class="bi bi-check-circle-fill"></i> Verified</span>
                      @else
                        <span class="status-badge badge-inactive"><i class="bi bi-hourglass-split"></i> Pending</span>
                      @endif
                    </td>
                    <td>
                      @if ($batch->is_verified)
                        <div style="font-size:0.85rem; color:#555;">
                          <span>By: {{ $batch->verifier->name }}</span><br/>
                          <small style="color:#888;">At: {{ $batch->verified_at->format('d M Y, h:i A') }}</small>
                        </div>
                      @else
                        <span style="color:#aaa;">Awaiting review</span>
                      @endif
                    </td>
                    @if ($user->isSuperAdmin())
                      <td>
                        <button class="danger-ghost btn-sm" onclick="openDeleteModal({{ $batch->id }})" title="Delete Batch">
                          <i class="bi bi-trash"></i> Delete
                        </button>
                      </td>
                    @endif
                  </tr>
                @empty
                  <tr>
                    <td colspan="{{ $user->isSuperAdmin() ? 9 : 8 }}" class="empty-row">No production batches logged.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if ($batches->hasPages())
            <div class="pagination-wrap">{{ $batches->links() }}</div>
          @endif
        </section>
      </main>
    </div>

    {{-- ══════════ DELETE BATCH MODAL ══════════ --}}
    @if ($user->isSuperAdmin())
      <div class="inv-modal-overlay" id="deleteModal">
        <div class="inv-modal" style="max-width: 500px;">
          <div class="inv-modal-head">
            <h3>Delete Production Batch</h3>
            <button class="inv-modal-close" id="closeDeleteModal" type="button"><i class="bi bi-x-lg"></i></button>
          </div>
          <form method="POST" action="" id="deleteForm" class="inv-modal-form">
            @csrf
            @method('DELETE')
            <div class="inv-modal-body">
              <div class="lp-error" style="background:#fff3cd; color:#856404; border-color:#ffeeba; margin-bottom:16px;">
                <i class="bi bi-exclamation-triangle-fill"></i> Warning: Deleting a batch removes its quantity from stock. This action will be audited.
              </div>

              <div style="background:#f8f9fa; padding:12px; border-radius:6px; border:1px solid #e9ecef; margin-bottom:16px; font-size:0.9rem; line-height:1.5;">
                <div><strong>Batch Number:</strong> <span id="delBatchNum">-</span></div>
                <div><strong>Product:</strong> <span id="delBatchProd">-</span></div>
                <div><strong>Quantity:</strong> <span id="delBatchQty">-</span></div>
              </div>

              <div class="form-group" style="text-align: center; margin-bottom: 20px;">
                <p style="margin: 0 0 10px; font-size:0.9rem; font-weight:500;">Step 1: Download Batch details report *</p>
                <a href="" target="_blank" class="primary-btn btn-sm" id="downloadReportBtn" style="background:#1565c0; border-color:#1565c0; text-decoration:none; display:inline-block; padding: 6px 16px;">
                  <i class="bi bi-file-earmark-arrow-down"></i> Download Report (CSV)
                </a>
              </div>

              <div id="deleteInputSection" style="display:none;">
                <div class="form-group">
                  <label for="reason">Step 2: Reason for Deletion * (Min 10 characters)</label>
                  <textarea class="form-input" id="reason" name="reason" rows="3" required placeholder="Provide a detailed explanation for auditing purposes..."></textarea>
                </div>
              </div>
            </div>
            <div class="inv-modal-footer">
              <button type="button" class="ghost-btn" id="cancelDeleteModal">Cancel</button>
              <button type="submit" class="primary-btn" id="confirmDeleteBtn" style="background:#b71c1c; border-color:#b71c1c;" disabled><i class="bi bi-trash"></i> Delete Batch</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Batch data for JS delete modal --}}
      @php
        $batchData = $batches->getCollection()->mapWithKeys(fn($b) => [$b->id => [
          'batch_number' => $b->batch_number,
          'product_name' => $b->product->name,
          'quantity'     => $b->quantity,
        ]]);
      @endphp

      <script>
        const batchData = @json($batchData);
        let selectedBatchId = null;

        function openDeleteModal(id) {
          const b = batchData[id];
          if (!b) return;

          selectedBatchId = id;
          document.getElementById('delBatchNum').textContent = b.batch_number;
          document.getElementById('delBatchProd').textContent = b.product_name;
          document.getElementById('delBatchQty').textContent = parseFloat(b.quantity).toLocaleString();

          // Set actions
          document.getElementById('deleteForm').action = `/admin/inventory/batches/${id}`;
          document.getElementById('downloadReportBtn').href = `/admin/inventory/batches/${id}/download`;

          // Reset inputs
          document.getElementById('reason').value = '';
          document.getElementById('deleteInputSection').style.display = 'none';
          document.getElementById('confirmDeleteBtn').setAttribute('disabled', 'true');

          document.getElementById('deleteModal').classList.add('active');
          document.body.style.overflow = 'hidden';
        }

        function closeDelete() {
          document.getElementById('deleteModal').classList.remove('active');
          document.body.style.overflow = '';
        }

        document.getElementById('closeDeleteModal')?.addEventListener('click', closeDelete);
        document.getElementById('cancelDeleteModal')?.addEventListener('click', closeDelete);
        document.getElementById('deleteModal')?.addEventListener('click', e => {
          if (e.target === e.currentTarget) closeDelete();
        });

        // Enable deletion section upon report download
        document.getElementById('downloadReportBtn')?.addEventListener('click', function() {
          setTimeout(() => {
            document.getElementById('deleteInputSection').style.display = 'block';
          }, 500);
        });

        // Enable submit button when reason is entered
        document.getElementById('reason')?.addEventListener('input', function() {
          const val = this.value.trim();
          const btn = document.getElementById('confirmDeleteBtn');
          if (val.length >= 10) {
            btn.removeAttribute('disabled');
          } else {
            btn.setAttribute('disabled', 'true');
          }
        });
      </script>
    @endif

    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
