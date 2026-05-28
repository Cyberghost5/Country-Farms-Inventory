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
            <p>Active product directory — view product specifications.</p>
          </div>
        </header>

        {{-- Filters --}}
        <section class="card inv-filter-bar">
          <form method="GET" action="{{ route('production.products.index') }}" class="inv-filters">
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
              <a href="{{ route('production.products.index') }}" class="ghost-btn">Clear</a>
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
                </tr>
              </thead>
              <tbody>
                @forelse ($products as $product)
                  <tr>
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
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="empty-row">
                      <i class="bi bi-box-seam" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#ccc;"></i>
                      No products found.
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
