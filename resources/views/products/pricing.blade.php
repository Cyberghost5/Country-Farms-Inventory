<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Distributor Pricing - Country Yoghurt MD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
      .select2-container {
        width: 100% !important;
      }
      .select2-container--default .select2-selection--multiple {
        border: 1.5px solid var(--border, #e2e8f0) !important;
        border-radius: 10px !important;
        padding: 4px 8px !important;
        background: #fefdfb !important;
        font-family: 'Poppins', sans-serif !important;
        font-size: 0.84rem !important;
        min-height: 42px !important;
        box-sizing: border-box !important;
      }
      .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #1d086c !important;
        outline: 2px solid rgba(29, 8, 108, 0.15) !important;
      }
      .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #e8f4e8 !important;
        border: 1px solid #3a6b35 !important;
        color: #3a6b35 !important;
        border-radius: 6px !important;
        padding: 2px 8px !important;
        font-weight: 500 !important;
        font-size: 0.78rem !important;
      }
      .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #3a6b35 !important;
        margin-right: 5px !important;
        border: none !important;
        background: none !important;
      }
      .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background-color: transparent !important;
        color: #b91c1c !important;
      }
      .select2-dropdown {
        border: 1.5px solid var(--border, #e2e8f0) !important;
        border-radius: 10px !important;
        font-family: 'Poppins', sans-serif !important;
        font-size: 0.84rem !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
        z-index: 99999 !important;
      }
      .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3a6b35 !important;
        color: white !important;
      }
      /* Ensure select2 displays properly on top of modal overlays */
      .select2-container--open {
        z-index: 9999999 !important;
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
            <h2>Distributor Pricing</h2>
            <p>Set custom product prices and discounts per distributor.</p>
          </div>
        </header>

        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif

        {{-- Distributor selector --}}
        <section class="card" style="margin-bottom:16px;padding:18px 24px;">
          <form method="GET" action="{{ route('admin.pricing.index') }}" class="inv-filters">
            <label style="font-weight:600;font-size:.9rem;color:#1d086c;">Distributor:</label>
            <select name="distributor_id" class="filter-select" onchange="this.form.submit()" style="min-width:220px;">
              @foreach ($distributors as $dist)
                <option value="{{ $dist->id }}" {{ $selected?->id === $dist->id ? 'selected' : '' }}>
                  {{ $dist->name }} {{ $dist->company_name ? '- '.$dist->company_name : '' }}
                </option>
              @endforeach
            </select>
          </form>
        </section>

        @if ($selected)
          {{-- Pricing table --}}
          <section class="card table-card" style="margin-bottom:24px;">
            <div class="section-head" style="padding:18px 24px 0;">
              <h3 style="font-size:1rem;color:#1d086c;font-weight:600;">
                <i class="bi bi-tags"></i> Product Prices for {{ $selected->name }}
              </h3>
              <p style="color:#888;font-size:.85rem;margin:4px 0 16px;">
                Leave price blank to use the global base price.
              </p>
            </div>
            <form method="POST" action="{{ route('admin.pricing.save') }}">
              @csrf
              <input type="hidden" name="distributor_id" value="{{ $selected->id }}" />
              <div class="table-scroll">
                <table class="inv-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Category</th>
                      <th>Unit</th>
                      <th>Base Price (₦)</th>
                      <th>Custom Price (₦)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($products as $product)
                      <tr>
                        <td>
                          <span class="inv-name">{{ $product->name }}</span>
                          <span class="sku-code" style="font-size:.75rem;color:#999;">{{ $product->sku }}</span>
                        </td>
                        <td><span class="cat-pill cat-{{ $product->category }}">{{ ucfirst($product->category) }}</span></td>
                        <td>{{ ucfirst($product->unit) }}</td>
                        <td>{{ number_format($product->base_price, 2) }}</td>
                        <td>
                          <input type="number"
                                 name="prices[{{ $product->id }}]"
                                 class="form-input pricing-input"
                                 value="{{ isset($pricing[$product->id]) ? number_format((float)$pricing[$product->id], 2, '.', '') : '' }}"
                                 step="0.01" min="0"
                                 placeholder="{{ number_format($product->base_price, 2) }}" />
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div style="padding:16px 24px;border-top:1px solid #f0ebe0;text-align:right;">
                <button type="submit" class="primary-btn"><i class="bi bi-save"></i> Save Pricing</button>
              </div>
            </form>
          </section>

          {{-- Discounts section --}}
          <section class="card" style="padding:0 0 24px;">
            <div class="section-head" style="padding:18px 24px 12px;border-bottom:1px solid #f0ebe0;display:flex;align-items:center;justify-content:space-between;">
              <div>
                <h3 style="font-size:1rem;color:#1d086c;font-weight:600;"><i class="bi bi-percent"></i> State-wide Discounts</h3>
                <p style="color:#888;font-size:.85rem;margin-top:4px;">Active state-wide discount rules applied at order/dispatch time.</p>
              </div>
              <button class="primary-btn" id="openDiscountModal">
                <i class="bi bi-plus-lg"></i> Add Discount
              </button>
            </div>

            @if ($discounts->isEmpty())
              <p style="padding:20px 24px;color:#999;font-size:.9rem;">No state-wide discount rules set.</p>
            @else
              <div class="table-scroll">
                <table class="inv-table">
                  <thead>
                    <tr>
                      <th>State</th>
                      <th>Type</th>
                      <th>Applies To</th>
                      <th>Description</th>
                      <th>Notes</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($discounts as $disc)
                      <tr>
                        <td><strong>{{ $disc->state }}</strong></td>
                        <td>{{ ucfirst($disc->type) }}</td>
                        <td>
                          {{ ucfirst($disc->applies_to) }}@if ($disc->applies_value): {{ $disc->applies_to === 'product' && $disc->product ? $disc->product->name : ucfirst($disc->applies_value) }}@endif
                        </td>
                        <td>{{ $disc->label }}</td>
                        <td>{{ $disc->notes ?: '-' }}</td>
                        <td>
                          <form method="POST" action="{{ route('admin.pricing.destroyDiscount', $disc) }}"
                                onsubmit="return confirm('Remove this discount?')">
                            @csrf @method('DELETE')
                            <button class="danger-ghost btn-sm"><i class="bi bi-trash"></i></button>
                          </form>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </section>
        @else
          <div class="card" style="padding:40px;text-align:center;color:#888;">
            <i class="bi bi-people" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
            No active distributors found. <a href="{{ route('admin.users.index', ['role'=>'distributor']) }}" class="link-btn">Add a distributor first.</a>
          </div>
        @endif

      </main>
    </div>

    {{-- ══════════ ADD DISCOUNT MODAL ══════════ --}}
    <div class="inv-modal-overlay" id="discountModal">
      <div class="inv-modal">
        <div class="inv-modal-head">
          <h3>Add Discount Rule</h3>
          <button class="inv-modal-close" id="closeDiscountModal" type="button"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.pricing.storeDiscount') }}" class="inv-modal-form">
          @csrf

          <div class="inv-modal-body">
            <div class="form-row-2">
              <div class="form-group">
                <label>State *</label>
                <select class="form-input" name="state" id="stateSelect" required>
                  <option value="">-- Select State --</option>
                </select>
              </div>
              <div class="form-group">
                <label>Discount Type *</label>
                <select class="form-input" name="type" required id="discountTypeSelect">
                  <option value="percentage">Percentage (%)</option>
                  <option value="fixed">Fixed Amount (₦)</option>
                </select>
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Value *</label>
                <input class="form-input" type="number" name="value" step="0.01" min="0" required
                       placeholder="e.g. 10 for 10%" />
              </div>
              <div class="form-group">
                <label>Applies To *</label>
                <select class="form-input" name="applies_to" required id="appliesToSelect">
                  <option value="all">All Products</option>
                  <option value="category">Specific Category</option>
                  <option value="product">Specific Product</option>
                </select>
              </div>
            </div>

            <div class="form-row-2" style="margin-top: 15px;">
              <div class="form-group" id="categoriesWrap" style="display:none; width: 100%;">
                <label>Select Category/Categories *</label>
                <select class="form-input" name="applies_value_categories[]" id="categoriesSelect" multiple>
                  @foreach (\App\Models\Product::CATEGORIES as $cat)
                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                  @endforeach
                </select>
                <small style="color: #888; font-size: 0.72rem; display: block; margin-top: 4px;">Search and select one or more categories.</small>
              </div>
              <div class="form-group" id="productsWrap" style="display:none; width: 100%;">
                <label>Select Product(s) *</label>
                <select class="form-input" name="applies_value_products[]" id="productsSelect" multiple>
                  @foreach ($products as $prod)
                    <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                  @endforeach
                </select>
                <small style="color: #888; font-size: 0.72rem; display: block; margin-top: 4px;">Search and select one or more products.</small>
              </div>
            </div>

            <div class="form-group">
              <label>Notes</label>
              <input class="form-input" type="text" name="notes" placeholder="Optional notes" />
            </div>
          </div>

          <div class="inv-modal-footer">
            <button type="button" class="ghost-btn" id="cancelDiscountModal">Cancel</button>
            <button type="submit" class="primary-btn"><i class="bi bi-check-lg"></i> Add Discount</button>
          </div>
        </form>
      </div>
    </div>

    <!-- jQuery and Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
      // Discount modal (Vanilla JS)
      document.getElementById('openDiscountModal')?.addEventListener('click', () => {
        document.getElementById('discountModal').classList.add('active');
        document.body.style.overflow = 'hidden';
      });
      ['closeDiscountModal','cancelDiscountModal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', closeDiscount);
      });
      document.getElementById('discountModal')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeDiscount();
      });
      function closeDiscount() {
        document.getElementById('discountModal').classList.remove('active');
        document.body.style.overflow = '';
      }

      // jQuery / Select2 Logic
      $(document).ready(function() {
        // Initialize Select2 search dropdowns
        $('#categoriesSelect').select2({
          placeholder: "Select category/categories",
          allowClear: true,
          width: '100%'
        });
        $('#productsSelect').select2({
          placeholder: "Select product(s)",
          allowClear: true,
          width: '100%'
        });

        // Show/hide applies_value fields
        $('#appliesToSelect').on('change', function() {
          const val = $(this).val();
          if (val === 'all') {
            $('#categoriesWrap').hide();
            $('#productsWrap').hide();
          } else if (val === 'category') {
            $('#categoriesWrap').show();
            $('#productsWrap').hide();
          } else if (val === 'product') {
            $('#categoriesWrap').hide();
            $('#productsWrap').show();
          }
        });

        // Custom validation on submission to avoid browser validation focus issues with hidden Select2 controls
        $('.inv-modal-form').on('submit', function(e) {
          const appliesTo = $('#appliesToSelect').val();
          if (appliesTo === 'category') {
            const selectedCats = $('#categoriesSelect').val();
            if (!selectedCats || selectedCats.length === 0) {
              alert('Please select at least one category.');
              e.preventDefault();
              return false;
            }
          } else if (appliesTo === 'product') {
            const selectedProds = $('#productsSelect').val();
            if (!selectedProds || selectedProds.length === 0) {
              alert('Please select at least one product.');
              e.preventDefault();
              return false;
            }
          }
        });
      });
    </script>
    <script src="{{ asset('assets/js/states-lgas.js') }}"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const stateSelect = document.getElementById('stateSelect');
        const selectedDistributorState = "{{ $selected?->state }}";

        if (window.statesAndLgas && stateSelect) {
          // Populate states
          for (const state in window.statesAndLgas) {
            if (window.statesAndLgas.hasOwnProperty(state)) {
              if (state === 'FCT') continue; // Skip redundant alias key in list
              const opt = document.createElement('option');
              opt.value = state;
              opt.textContent = state;
              if (state === selectedDistributorState) {
                opt.selected = true;
              }
              stateSelect.appendChild(opt);
            }
          }
        }
      });
    </script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
