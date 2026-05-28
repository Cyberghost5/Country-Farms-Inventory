<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Record Dispatch - Country Yoghurt MD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <style>
      .dispatch-row {
        background: #fdfdfd;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        position: relative;
      }
      .dispatch-row-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
      }
      .stock-badge-low {
        color: #b71c1c;
        font-weight: 600;
      }
      .total-amount-box {
        background: #f5f4fd;
        border: 1px solid #d4cbf5;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        text-align: right;
      }
      @media (max-width: 768px) {
        .dispatch-row-grid {
          grid-template-columns: 1fr;
          gap: 10px;
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
            <h2>Record Dispatch</h2>
            <p>Store Manager panel — record new dispatch and auto-generate invoice.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('store.dispatches.index') }}" class="ghost-btn" style="text-decoration:none;">
              <i class="bi bi-arrow-left"></i> Back to Log
            </a>
          </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('store.dispatches.store') }}" id="dispatchForm">
          @csrf

          <div class="card" style="padding:24px; margin-bottom:20px;">
            <h3 style="color:#1d086c; margin-bottom:16px;">Dispatch Details</h3>
            <div class="form-row-2">
              <div class="form-group">
                <label for="distributor_id">Select Distributor *</label>
                <select class="form-input" id="distributor_id" name="distributor_id" required>
                  <option value="">-- Select Distributor --</option>
                  @foreach ($distributors as $d)
                    <option value="{{ $d->id }}">{{ $d->company_name ?: $d->name }} ({{ $d->name }})</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group">
                <label for="remarks">Remarks</label>
                <input class="form-input" type="text" id="remarks" name="remarks" placeholder="Optional notes about transport, vehicle, driver, etc." />
              </div>
            </div>
          </div>

          <div class="card" style="padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
              <h3 style="color:#1d086c; margin:0;">Dispatch Items</h3>
              <button type="button" class="ghost-btn btn-sm" id="addRowBtn">
                <i class="bi bi-plus-lg"></i> Add Item
              </button>
            </div>

            <div id="dispatchItemsContainer">
              {{-- Rows will be appended here dynamically by JS --}}
            </div>

            <div class="total-amount-box">
              <span style="font-size:1.1rem; color:#666;">Total Value:</span>
              <h2 style="color:#1d086c; margin: 4px 0 0;" id="totalAmountDisp">₦0.00</h2>
            </div>

            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:12px;">
              <a href="{{ route('store.dispatches.index') }}" class="ghost-btn" style="text-decoration:none;">Cancel</a>
              <button type="submit" class="primary-btn" id="submitBtn" disabled>
                <i class="bi bi-check-lg"></i> Complete Dispatch
              </button>
            </div>
          </div>
        </form>
      </main>
    </div>

    {{-- Script data preloading --}}
    <script>
      const pricingMap = @json($pricingMap);
      const products = @json($products);
      let rowIndex = 0;

      document.addEventListener('DOMContentLoaded', () => {
        // Add initial row
        addNewRow();

        // Register handlers
        document.getElementById('distributor_id').addEventListener('change', () => {
          updateAllPrices();
          updateTotal();
        });
        document.getElementById('addRowBtn').addEventListener('click', addNewRow);

        document.getElementById('dispatchForm').addEventListener('submit', (e) => {
          if (!validateStock()) {
            e.preventDefault();
          }
        });
      });

      function addNewRow() {
        const container = document.getElementById('dispatchItemsContainer');
        const idx = rowIndex++;

        const row = document.createElement('div');
        row.className = 'dispatch-row';
        row.id = `row-${idx}`;

        const prodOptions = products.map(p => 
          `<option value="${p.id}" data-stock="${p.available_stock}">${esc(p.name)} (SKU: ${esc(p.sku)})</option>`
        ).join('');

        row.innerHTML = `
          <div class="dispatch-row-grid">
            <div class="form-group" style="margin:0;">
              <label>Product *</label>
              <select class="form-input product-select" name="items[${idx}][product_id]" required>
                <option value="">-- Choose Product --</option>
                ${prodOptions}
              </select>
            </div>
            <div class="form-group" style="margin:0;">
              <label>Quantity *</label>
              <input class="form-input qty-input" type="number" name="items[${idx}][quantity]" min="1" required disabled />
            </div>
            <div class="form-group" style="margin:0;">
              <label>Unit Price (₦)</label>
              <input class="form-input price-input" type="text" readonly value="—" />
            </div>
            <div class="form-group" style="margin:0;">
              <label>Subtotal (₦)</label>
              <input class="form-input subtotal-input" type="text" readonly value="—" />
            </div>
            <div>
              <button type="button" class="danger-ghost btn-sm remove-row-btn" onclick="removeRow(${idx})">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
          <div style="margin-top:8px; display:flex; justify-content:space-between;">
            <span class="stock-indicator" style="font-size:0.8rem; color:#666; visibility:hidden;">
              Available Stock: <strong class="stock-qty">0</strong>
            </span>
            <span class="stock-error" style="font-size:0.8rem; color:#b71c1c; font-weight:600; display:none;">
              Insufficient stock!
            </span>
          </div>
        `;

        container.appendChild(row);

        // Hook fields change events
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');

        select.addEventListener('change', () => handleProductSelect(row));
        qtyInput.addEventListener('input', () => handleQtyChange(row));

        updateSubmitState();
      }

      function removeRow(idx) {
        const row = document.getElementById(`row-${idx}`);
        row?.remove();
        updateTotal();
        updateSubmitState();
      }

      function handleProductSelect(row) {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const stockInd = row.querySelector('.stock-indicator');
        const stockQty = row.querySelector('.stock-qty');
        const priceInput = row.querySelector('.price-input');
        const subtotalInput = row.querySelector('.subtotal-input');
        const stockErr = row.querySelector('.stock-error');

        const prodId = select.value;
        const distId = document.getElementById('distributor_id').value;

        if (!prodId) {
          qtyInput.disabled = true;
          qtyInput.value = '';
          stockInd.style.visibility = 'hidden';
          priceInput.value = '—';
          subtotalInput.value = '—';
          stockErr.style.display = 'none';
          updateTotal();
          return;
        }

        const selectedOption = select.options[select.selectedIndex];
        const stock = parseInt(selectedOption.getAttribute('data-stock'));

        qtyInput.disabled = false;
        if (!qtyInput.value) qtyInput.value = 1;

        stockQty.textContent = numberFormat(stock);
        stockInd.style.visibility = 'visible';

        if (distId && pricingMap[distId] && pricingMap[distId][prodId] !== undefined) {
          const price = pricingMap[distId][prodId];
          priceInput.value = numberFormat(price, 2);
          subtotalInput.value = numberFormat(price * qtyInput.value, 2);
        } else {
          // Default to product base price
          const prodObj = products.find(p => p.id == prodId);
          const price = prodObj ? prodObj.base_price : 0;
          priceInput.value = numberFormat(price, 2);
          subtotalInput.value = numberFormat(price * qtyInput.value, 2);
        }

        validateRowStock(row);
        updateTotal();
      }

      function handleQtyChange(row) {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const subtotalInput = row.querySelector('.subtotal-input');

        const distId = document.getElementById('distributor_id').value;
        const prodId = select.value;
        const qty = parseInt(qtyInput.value) || 0;

        let price = 0;
        if (prodId && distId && pricingMap[distId] && pricingMap[distId][prodId] !== undefined) {
          price = pricingMap[distId][prodId];
        } else if (prodId) {
          const prodObj = products.find(p => p.id == prodId);
          price = prodObj ? prodObj.base_price : 0;
        }

        subtotalInput.value = numberFormat(price * qty, 2);

        validateRowStock(row);
        updateTotal();
      }

      function updateAllPrices() {
        const distId = document.getElementById('distributor_id').value;
        const rows = document.querySelectorAll('.dispatch-row');

        rows.forEach(row => {
          const select = row.querySelector('.product-select');
          const qtyInput = row.querySelector('.qty-input');
          const priceInput = row.querySelector('.price-input');
          const subtotalInput = row.querySelector('.subtotal-input');

          const prodId = select.value;
          if (!prodId) return;

          const qty = parseInt(qtyInput.value) || 0;

          if (distId && pricingMap[distId] && pricingMap[distId][prodId] !== undefined) {
            const price = pricingMap[distId][prodId];
            priceInput.value = numberFormat(price, 2);
            subtotalInput.value = numberFormat(price * qty, 2);
          }
        });
      }

      function validateRowStock(row) {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const stockQty = row.querySelector('.stock-qty');
        const stockErr = row.querySelector('.stock-error');

        const prodId = select.value;
        if (!prodId) return true;

        const selectedOption = select.options[select.selectedIndex];
        const stock = parseInt(selectedOption.getAttribute('data-stock'));
        const qty = parseInt(qtyInput.value) || 0;

        if (qty > stock) {
          stockQty.parentElement.classList.add('stock-badge-low');
          stockErr.style.display = 'block';
          return false;
        } else {
          stockQty.parentElement.classList.remove('stock-badge-low');
          stockErr.style.display = 'none';
          return true;
        }
      }

      function validateStock() {
        let valid = true;
        const rows = document.querySelectorAll('.dispatch-row');
        rows.forEach(row => {
          if (!validateRowStock(row)) {
            valid = false;
          }
        });

        if (!valid) {
          alert('One or more items exceed available stock levels. Please correct quantities.');
        }
        return valid;
      }

      function updateTotal() {
        const distId = document.getElementById('distributor_id').value;
        const rows = document.querySelectorAll('.dispatch-row');
        let total = 0;

        rows.forEach(row => {
          const select = row.querySelector('.product-select');
          const qtyInput = row.querySelector('.qty-input');

          const prodId = select.value;
          if (!prodId) return;

          const qty = parseInt(qtyInput.value) || 0;
          let price = 0;

          if (distId && pricingMap[distId] && pricingMap[distId][prodId] !== undefined) {
            price = pricingMap[distId][prodId];
          } else {
            const prodObj = products.find(p => p.id == prodId);
            price = prodObj ? prodObj.base_price : 0;
          }

          total += price * qty;
        });

        document.getElementById('totalAmountDisp').textContent = '₦' + numberFormat(total, 2);
        updateSubmitState();
      }

      function updateSubmitState() {
        const distId = document.getElementById('distributor_id').value;
        const rowsCount = document.querySelectorAll('.dispatch-row').length;
        const submitBtn = document.getElementById('submitBtn');

        if (distId && rowsCount > 0) {
          submitBtn.removeAttribute('disabled');
        } else {
          submitBtn.setAttribute('disabled', 'true');
        }
      }

      function esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
      }

      function numberFormat(number, decimals = 0) {
        return parseFloat(number).toLocaleString('en-US', {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals
        });
      }
    </script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
