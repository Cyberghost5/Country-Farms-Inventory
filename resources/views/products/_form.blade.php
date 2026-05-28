{{-- Shared product form fields (used in add modal) --}}

<div class="form-row-2">
  <div class="form-group">
    <label>Product Name *</label>
    <input class="form-input" type="text" name="name"
           value="{{ old('name', $product?->name) }}" required />
  </div>
  <div class="form-group">
    <label>SKU</label>
    <input class="form-input" type="text" name="sku"
           value="{{ old('sku', $product?->sku) }}"
           placeholder="Leave blank to auto-generate" />
  </div>
</div>

<div class="form-row-2">
  <div class="form-group">
    <label>Category *</label>
    <select class="form-input" name="category" required>
      <option value="">Select category…</option>
      @foreach (\App\Models\Product::CATEGORIES as $cat)
        <option value="{{ $cat }}"
          {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>
          {{ ucfirst($cat) }}
        </option>
      @endforeach
    </select>
  </div>
  <div class="form-group">
    <label>Unit *</label>
    <select class="form-input" name="unit" required>
      <option value="">Select unit…</option>
      @foreach (\App\Models\Product::UNITS as $unit)
        <option value="{{ $unit }}"
          {{ old('unit', $product?->unit) === $unit ? 'selected' : '' }}>
          {{ ucfirst($unit) }}
        </option>
      @endforeach
    </select>
  </div>
</div>

<div class="form-row-2">
  <div class="form-group">
    <label>Size / Volume</label>
    <input class="form-input" type="text" name="size_volume"
           value="{{ old('size_volume', $product?->size_volume) }}"
           placeholder="e.g. 250ml, 1 litre" />
  </div>
  <div class="form-group">
    <label>Packaging Type</label>
    <input class="form-input" type="text" name="packaging_type"
           value="{{ old('packaging_type', $product?->packaging_type) }}"
           placeholder="e.g. Sachet, Carton" />
  </div>
</div>

<div class="form-row-2">
  <div class="form-group">
    <label>Base Price (₦) *</label>
    <input class="form-input" type="number" name="base_price"
           value="{{ old('base_price', $product?->base_price ?? 0) }}"
           step="0.01" min="0" required />
  </div>
  @if ($product)
    <div class="form-group">
      <label>Status</label>
      <select class="form-input" name="is_active">
        <option value="1" {{ old('is_active', $product->is_active) ? 'selected' : '' }}>Active</option>
        <option value="0" {{ !old('is_active', $product->is_active) ? 'selected' : '' }}>Inactive</option>
      </select>
    </div>
  @endif
</div>

<div class="form-group">
  <label>Description</label>
  <textarea class="form-input" name="description" rows="2"
            placeholder="Optional product description…">{{ old('description', $product?->description) }}</textarea>
</div>
