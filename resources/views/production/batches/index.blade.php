<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Inventory - Country Yoghurt MD</title>
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
            <h2>Upload Inventory</h2>
            <p>Production Manager panel - log and edit inventory batches.</p>
          </div>
          <div class="top-actions">
            <button class="primary-btn" id="openAddModal">
              <i class="bi bi-plus-lg"></i> Upload Batch
            </button>
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
        <section class="kpi-grid" style="margin-bottom:16px;">
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon"><i class="bi bi-clipboard-plus"></i></span></div>
            <h4 class="stat-value">{{ $stats['total'] }}</h4>
            <small class="stat-label">Total Uploads</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#fff3cd;color:#856404;"><i class="bi bi-hourglass-split"></i></span></div>
            <h4 class="stat-value">{{ $stats['pending'] }}</h4>
            <small class="stat-label">Pending Verification</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#d1e7dd;color:#0a3622;"><i class="bi bi-check-circle"></i></span></div>
            <h4 class="stat-value">{{ $stats['verified'] }}</h4>
            <small class="stat-label">Verified</small>
          </article>
        </section>

        {{-- Filters --}}
        <section class="card inv-filter-bar">
          <form method="GET" action="{{ route('production.batches.index') }}" class="inv-filters">
            <label class="search-wrap inv-search">
              <i class="bi bi-search search-icon"></i>
              <input type="search" name="search" placeholder="Search batch number…" value="{{ $search }}" />
            </label>
            <select name="status" class="filter-select" onchange="this.form.submit()">
              <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
              <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="verified" {{ $status === 'verified' ? 'selected' : '' }}>Verified</option>
            </select>
            <button type="submit" class="ghost-btn">Apply</button>
            @if ($search || $status !== 'all')
              <a href="{{ route('production.batches.index') }}" class="ghost-btn">Clear</a>
            @endif
          </form>
          <span class="inv-count">{{ $batches->total() }} batch{{ $batches->total() !== 1 ? 'es' : '' }}</span>
        </section>

        {{-- Table --}}
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
                  <th>Status</th>
                  <th>Remarks</th>
                  <th>Actions</th>
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
                    <td>
                      @if ($batch->is_verified)
                        <span class="status-badge badge-active"><i class="bi bi-check-circle-fill"></i> Verified</span>
                      @else
                        <span class="status-badge badge-inactive"><i class="bi bi-hourglass-split"></i> Pending</span>
                      @endif
                    </td>
                    <td>
                      @if ($batch->remarks)
                        <span class="inv-notes" title="{{ $batch->remarks }}">{{ Str::limit($batch->remarks, 40) }}</span>
                      @else
                        <span style="color:#aaa;">-</span>
                      @endif
                    </td>
                    <td>
                      <div class="inv-actions">
                        @if (!$batch->is_verified)
                          <button class="ghost-btn btn-sm" onclick="openEditModal({{ $batch->id }})" title="Edit Batch">
                            <i class="bi bi-pencil"></i> Edit
                          </button>
                        @else
                          <span style="color:#2e7d32; font-size:0.9rem;" title="Locked (Verified)">
                            <i class="bi bi-lock-fill"></i> Locked
                          </span>
                        @endif
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="empty-row">
                      <i class="bi bi-clipboard" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#ccc;"></i>
                      No batches found. <button class="link-btn" id="openAddModalEmpty">Upload the first batch.</button>
                    </td>
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

    {{-- ══════════ UPLOAD BATCH MODAL ══════════ --}}
    <div class="inv-modal-overlay" id="addModal">
      <div class="inv-modal">
        <div class="inv-modal-head">
          <h3>Upload Production Batch</h3>
          <button class="inv-modal-close" id="closeAddModal" type="button"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('production.batches.store') }}" class="inv-modal-form">
          @csrf
          <div class="inv-modal-body">
            <div class="form-group">
              <label>Select Product *</label>
              <select class="form-input" name="product_id" required>
                <option value="">-- Choose Active Product --</option>
                @foreach ($products as $p)
                  <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                @endforeach
              </select>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Quantity Produced *</label>
                <input class="form-input" type="number" name="quantity" min="1" required placeholder="e.g. 500" />
              </div>
              <div class="form-group">
                <label>Batch Number *</label>
                <input class="form-input" type="text" name="batch_number" required placeholder="e.g. CY-BATCH-001" />
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Production Date *</label>
                <input class="form-input" type="date" name="production_date" required />
              </div>
              <div class="form-group">
                <label>Expiry Date *</label>
                <input class="form-input" type="date" name="expiry_date" required />
              </div>
            </div>

            <div class="form-group">
              <label>Remarks</label>
              <textarea class="form-input" name="remarks" rows="3" placeholder="Optional batch comments..."></textarea>
            </div>
          </div>
          <div class="inv-modal-footer">
            <button type="button" class="ghost-btn" id="cancelAddModal">Cancel</button>
            <button type="submit" class="primary-btn"><i class="bi bi-check-lg"></i> Upload Batch</button>
          </div>
        </form>
      </div>
    </div>

    {{-- ══════════ EDIT BATCH MODAL ══════════ --}}
    <div class="inv-modal-overlay" id="editModal">
      <div class="inv-modal">
        <div class="inv-modal-head">
          <h3>Edit Production Batch</h3>
          <button class="inv-modal-close" id="closeEditModal" type="button"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="editModalBody"></div>
      </div>
    </div>

    {{-- Batch data for JS edit --}}
    @php
      $batchData = $batches->getCollection()->mapWithKeys(fn($b) => [$b->id => [
        'product_id'      => $b->product_id,
        'quantity'        => $b->quantity,
        'batch_number'    => $b->batch_number,
        'production_date' => $b->production_date->format('Y-m-d'),
        'expiry_date'     => $b->expiry_date->format('Y-m-d'),
        'remarks'         => $b->remarks,
      ]]);
    @endphp

    <script>
      // Open/close add modal
      const openModalBtns = [document.getElementById('openAddModal'), document.getElementById('openAddModalEmpty')];
      openModalBtns.forEach(btn => btn?.addEventListener('click', () => {
        document.getElementById('addModal').classList.add('active');
        document.body.style.overflow = 'hidden';
      }));
      ['closeAddModal','cancelAddModal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', closeAdd);
      });
      document.getElementById('addModal')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeAdd();
      });
      function closeAdd() {
        document.getElementById('addModal').classList.remove('active');
        document.body.style.overflow = '';
      }

      // Edit modal
      const batchData = @json($batchData);
      const products = @json($products);

      function openEditModal(id) {
        const b = batchData[id];
        if (!b) return;

        const prodOptions = products.map(p =>
          `<option value="${p.id}" ${b.product_id === p.id ? 'selected' : ''}>${esc(p.name)} (SKU: ${esc(p.sku)})</option>`
        ).join('');

        document.getElementById('editModalBody').innerHTML = `
          <form method="POST" action="/production/batches/${id}" class="inv-modal-form">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">
            <div class="inv-modal-body">
              <div class="form-group">
                <label>Select Product *</label>
                <select class="form-input" name="product_id" required>${prodOptions}</select>
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label>Quantity Produced *</label>
                  <input class="form-input" type="number" name="quantity" value="${b.quantity}" min="1" required />
                </div>
                <div class="form-group">
                  <label>Batch Number *</label>
                  <input class="form-input" type="text" name="batch_number" value="${esc(b.batch_number)}" required />
                </div>
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label>Production Date *</label>
                  <input class="form-input" type="date" name="production_date" value="${b.production_date}" required />
                </div>
                <div class="form-group">
                  <label>Expiry Date *</label>
                  <input class="form-input" type="date" name="expiry_date" value="${b.expiry_date}" required />
                </div>
              </div>
              <div class="form-group">
                <label>Remarks</label>
                <textarea class="form-input" name="remarks" rows="3">${esc(b.remarks || '')}</textarea>
              </div>
            </div>
            <div class="inv-modal-footer">
              <button type="button" class="ghost-btn" onclick="closeEdit()">Cancel</button>
              <button type="submit" class="primary-btn"><i class="bi bi-check-lg"></i> Update Batch</button>
            </div>
          </form>`;

        document.getElementById('editModal').classList.add('active');
        document.body.style.overflow = 'hidden';
      }

      function closeEdit() {
        document.getElementById('editModal').classList.remove('active');
        document.body.style.overflow = '';
      }
      document.getElementById('closeEditModal')?.addEventListener('click', closeEdit);
      document.getElementById('editModal')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeEdit();
      });

      function esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
      }
    </script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
