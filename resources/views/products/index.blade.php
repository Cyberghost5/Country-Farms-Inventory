<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products - Country Yoghurt MD</title>
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
            <h2>Products</h2>
            <p>Master product list — all goods manufactured by Country Yoghurt.</p>
          </div>
          <div class="top-actions">
            <button class="primary-btn" id="openAddModal">
              <i class="bi bi-plus-lg"></i> Add Product
            </button>
          </div>
        </header>

        {{-- Flash --}}
        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- KPI bar --}}
        <section class="kpi-grid" style="margin-bottom:16px;">
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon"><i class="bi bi-box-seam"></i></span></div>
            <h4 class="stat-value">{{ $stats['total'] }}</h4>
            <small class="stat-label">Total Products</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#d1e7dd;color:#0a3622;"><i class="bi bi-check-circle"></i></span></div>
            <h4 class="stat-value">{{ $stats['active'] }}</h4>
            <small class="stat-label">Active</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#fff3cd;color:#856404;"><i class="bi bi-dash-circle"></i></span></div>
            <h4 class="stat-value">{{ $stats['inactive'] }}</h4>
            <small class="stat-label">Inactive</small>
          </article>
          <article class="stat-card">
            <div class="stat-top"><span class="mini-icon" style="background:#fdecea;color:#b71c1c;"><i class="bi bi-trash"></i></span></div>
            <h4 class="stat-value">{{ $stats['deleted'] }}</h4>
            <small class="stat-label">Deleted</small>
          </article>
        </section>

        {{-- Filters --}}
        <section class="card inv-filter-bar">
          <form method="GET" action="{{ route('admin.products.index') }}" class="inv-filters">
            <label class="search-wrap inv-search">
              <i class="bi bi-search search-icon"></i>
              <input type="search" name="search" placeholder="Search name or SKU…" value="{{ $search }}" />
            </label>
            <select name="category" class="filter-select" onchange="this.form.submit()">
              <option value="">All Categories</option>
              @foreach (\App\Models\Product::CATEGORIES as $cat)
                <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
              @endforeach
            </select>
            <button type="submit" class="ghost-btn">Apply</button>
            @if ($search || $category)
              <a href="{{ route('admin.products.index') }}" class="ghost-btn">Clear</a>
            @endif
          </form>
          <span class="inv-count">{{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }}</span>
        </section>

        {{-- Table --}}
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>SKU</th>
                  <th>Category</th>
                  <th>Size / Volume</th>
                  <th>Packaging</th>
                  <th>Unit</th>
                  <th>Base Price (₦)</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($products as $product)
                  <tr class="{{ $product->trashed() ? 'row-deleted' : '' }}">
                    <td>
                      <span class="inv-name">{{ $product->name }}</span>
                      @if ($product->description)
                        <span class="inv-notes">{{ Str::limit($product->description, 60) }}</span>
                      @endif
                    </td>
                    <td><code class="sku-code">{{ $product->sku }}</code></td>
                    <td><span class="cat-pill cat-{{ $product->category }}">{{ ucfirst($product->category) }}</span></td>
                    <td>{{ $product->size_volume ?: '—' }}</td>
                    <td>{{ $product->packaging_type ?: '—' }}</td>
                    <td>{{ ucfirst($product->unit) }}</td>
                    <td>{{ number_format($product->base_price, 2) }}</td>
                    <td>
                      @if ($product->trashed())
                        <span class="status-badge badge-deleted">Deleted</span>
                      @elseif ($product->is_active)
                        <span class="status-badge badge-active">Active</span>
                      @else
                        <span class="status-badge badge-inactive">Inactive</span>
                      @endif
                    </td>
                    <td>
                      <div class="inv-actions">
                        @if ($product->trashed())
                          <form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="ghost-btn btn-sm" title="Restore">
                              <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                          </form>
                        @else
                          <button class="ghost-btn btn-sm" onclick="openEditModal({{ $product->id }})" title="Edit">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                onsubmit="return confirm('Remove this product?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="danger-ghost btn-sm" title="Delete">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>
                        @endif
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="empty-row">
                      <i class="bi bi-box-seam" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#ccc;"></i>
                      No products found. <button class="link-btn" id="openAddModalEmpty">Add the first product.</button>
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

    {{-- ══════════ ADD PRODUCT MODAL ══════════ --}}
    <div class="inv-modal-overlay" id="addModal">
      <div class="inv-modal">
        <div class="inv-modal-head">
          <h3>Add New Product</h3>
          <button class="inv-modal-close" id="closeAddModal" type="button"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.products.store') }}" class="inv-modal-form">
          @csrf
          <div class="inv-modal-body">
            @include('products._form', ['product' => null])
          </div>
          <div class="inv-modal-footer">
            <button type="button" class="ghost-btn" id="cancelAddModal">Cancel</button>
            <button type="submit" class="primary-btn"><i class="bi bi-check-lg"></i> Save Product</button>
          </div>
        </form>
      </div>
    </div>

    {{-- ══════════ EDIT PRODUCT MODAL ══════════ --}}
    <div class="inv-modal-overlay" id="editModal">
      <div class="inv-modal">
        <div class="inv-modal-head">
          <h3>Edit Product</h3>
          <button class="inv-modal-close" id="closeEditModal" type="button"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="editModalBody">{{-- filled by JS --}}</div>
      </div>
    </div>

    {{-- Product data for JS edit --}}
    @php
      $productData = $products->getCollection()->mapWithKeys(fn($p) => [$p->id => [
        'name'           => $p->name,
        'sku'            => $p->sku,
        'category'       => $p->category,
        'size_volume'    => $p->size_volume,
        'packaging_type' => $p->packaging_type,
        'unit'           => $p->unit,
        'base_price'     => $p->base_price,
        'description'    => $p->description,
        'is_active'      => $p->is_active,
      ]]);
    @endphp

    <script>
      // ── Open/close add modal ──
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

      // ── Edit modal ──
      const productData = @json($productData);
      const categories  = @json(\App\Models\Product::CATEGORIES);
      const units       = @json(\App\Models\Product::UNITS);

      function openEditModal(id) {
        const p = productData[id];
        if (!p) return;

        const catOptions = categories.map(c =>
          `<option value="${c}" ${p.category === c ? 'selected' : ''}>${c.charAt(0).toUpperCase()+c.slice(1)}</option>`
        ).join('');
        const unitOptions = units.map(u =>
          `<option value="${u}" ${p.unit === u ? 'selected' : ''}>${u.charAt(0).toUpperCase()+u.slice(1)}</option>`
        ).join('');

        document.getElementById('editModalBody').innerHTML = `
          <form method="POST" action="/admin/products/${id}" class="inv-modal-form">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">
            <div class="inv-modal-body">
              <div class="form-row-2">
                <div class="form-group"><label>Product Name *</label><input class="form-input" type="text" name="name" value="${esc(p.name)}" required /></div>
                <div class="form-group"><label>SKU</label><input class="form-input" type="text" name="sku" value="${esc(p.sku||'')}" placeholder="Auto-generated" /></div>
              </div>
              <div class="form-row-2">
                <div class="form-group"><label>Category *</label><select class="form-input" name="category" required>${catOptions}</select></div>
                <div class="form-group"><label>Unit *</label><select class="form-input" name="unit" required>${unitOptions}</select></div>
              </div>
              <div class="form-row-2">
                <div class="form-group"><label>Size / Volume</label><input class="form-input" type="text" name="size_volume" value="${esc(p.size_volume||'')}" placeholder="e.g. 250ml" /></div>
                <div class="form-group"><label>Packaging Type</label><input class="form-input" type="text" name="packaging_type" value="${esc(p.packaging_type||'')}" placeholder="e.g. Carton" /></div>
              </div>
              <div class="form-row-2">
                <div class="form-group"><label>Base Price (₦) *</label><input class="form-input" type="number" name="base_price" value="${p.base_price}" step="0.01" min="0" required /></div>
                <div class="form-group">
                  <label>Status</label>
                  <select class="form-input" name="is_active">
                    <option value="1" ${p.is_active ? 'selected' : ''}>Active</option>
                    <option value="0" ${!p.is_active ? 'selected' : ''}>Inactive</option>
                  </select>
                </div>
              </div>
              <div class="form-group"><label>Description</label><textarea class="form-input" name="description" rows="2">${esc(p.description||'')}</textarea></div>
            </div>
            <div class="inv-modal-footer">
              <button type="button" class="ghost-btn" onclick="closeEdit()">Cancel</button>
              <button type="submit" class="primary-btn"><i class="bi bi-check-lg"></i> Update Product</button>
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
