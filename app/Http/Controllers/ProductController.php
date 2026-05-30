<?php

namespace App\Http\Controllers;

use App\Models\StateDiscount;
use App\Models\DistributorPricing;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // ── Product list ──────────────────────────────────────────────
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isSuperAdmin(), 403);

        $search   = $request->input('search', '');
        $category = $request->input('category', '');

        $products = Product::withTrashed()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"))
            ->when($category, fn($q) => $q->where('category', $category))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total'    => Product::count(),
            'active'   => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'deleted'  => Product::onlyTrashed()->count(),
        ];

        return view('products.index', compact('user', 'products', 'stats', 'search', 'category'));
    }

    // ── Store new product ──────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'sku'            => ['nullable', 'string', 'max:60', 'unique:products,sku'],
            'category'       => ['required', 'in:' . implode(',', Product::CATEGORIES)],
            'size_volume'    => ['nullable', 'string', 'max:50'],
            'packaging_type' => ['nullable', 'string', 'max:50'],
            'unit'           => ['required', 'in:' . implode(',', Product::UNITS)],
            'base_price'     => ['required', 'numeric', 'min:0'],
            'description'    => ['nullable', 'string'],
        ]);

        if (empty($data['sku'])) {
            $data['sku'] = Product::generateSku($data['category'], $data['name']);
        }
        $data['created_by'] = Auth::id();

        Product::create($data);

        return back()->with('success', 'Product "' . $data['name'] . '" created successfully.');
    }

    // ── Update product ─────────────────────────────────────────────
    public function update(Request $request, Product $product)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'sku'            => ['nullable', 'string', 'max:60', "unique:products,sku,{$product->id}"],
            'category'       => ['required', 'in:' . implode(',', Product::CATEGORIES)],
            'size_volume'    => ['nullable', 'string', 'max:50'],
            'packaging_type' => ['nullable', 'string', 'max:50'],
            'unit'           => ['required', 'in:' . implode(',', Product::UNITS)],
            'base_price'     => ['required', 'numeric', 'min:0'],
            'description'    => ['nullable', 'string'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        if (empty($data['sku'])) {
            $data['sku'] = $product->sku ?? Product::generateSku($data['category'], $data['name']);
        }
        $data['is_active'] = $request->boolean('is_active', true);

        $product->update($data);

        return back()->with('success', 'Product updated successfully.');
    }

    // ── Soft-delete product ────────────────────────────────────────
    public function destroy(Product $product)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $product->delete();

        return back()->with('success', 'Product "' . $product->name . '" removed.');
    }

    // ── Restore soft-deleted product ──────────────────────────────
    public function restore(int $id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return back()->with('success', 'Product "' . $product->name . '" restored.');
    }

    // ═════════════════════════════════════════════════════════════
    //  PRICING & DISCOUNTS
    // ═════════════════════════════════════════════════════════════

    public function pricingIndex(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isSuperAdmin(), 403);

        $distributors = User::where('role', 'distributor')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedId  = $request->input('distributor_id');
        $selected    = $selectedId ? User::find($selectedId) : $distributors->first();

        $products    = Product::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $pricing     = DistributorPricing::where('distributor_id', $selected?->id)->pluck('price', 'product_id');
        $discounts   = StateDiscount::with('product')->where('is_active', true)->orderBy('state')->get();

        return view('products.pricing', compact('user', 'distributors', 'selected', 'products', 'pricing', 'discounts'));
    }

    public function savePricing(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $request->validate([
            'distributor_id' => ['required', 'exists:users,id'],
            'prices'         => ['required', 'array'],
            'prices.*'       => ['nullable', 'numeric', 'min:0'],
        ]);

        $distributorId = $request->input('distributor_id');

        foreach ($request->input('prices', []) as $productId => $price) {
            if ($price === null || $price === '') {
                DistributorPricing::where('distributor_id', $distributorId)
                    ->where('product_id', $productId)
                    ->delete();
                continue;
            }
            DistributorPricing::updateOrCreate(
                ['distributor_id' => $distributorId, 'product_id' => $productId],
                ['price' => $price]
            );
        }

        return back()->with('success', 'Pricing saved successfully.');
    }

    public function storeDiscount(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $request->validate([
            'state'      => ['required', 'string'],
            'type'       => ['required', 'in:percentage,fixed'],
            'value'      => ['required', 'numeric', 'min:0'],
            'applies_to' => ['required', 'in:all,category,product'],
            'notes'      => ['nullable', 'string'],
        ]);

        $appliesTo = $request->input('applies_to');
        $state = $request->input('state');
        $type = $request->input('type');
        $value = $request->input('value');
        $notes = $request->input('notes');

        $values = [];
        if ($appliesTo === 'all') {
            $values[] = null;
        } elseif ($appliesTo === 'category') {
            $request->validate([
                'applies_value_categories' => ['required', 'array', 'min:1'],
                'applies_value_categories.*' => ['string'],
            ]);
            $values = $request->input('applies_value_categories');
        } elseif ($appliesTo === 'product') {
            $request->validate([
                'applies_value_products' => ['required', 'array', 'min:1'],
                'applies_value_products.*' => ['exists:products,id'],
            ]);
            $values = $request->input('applies_value_products');
        }

        foreach ($values as $val) {
            StateDiscount::create([
                'state'          => $state,
                'type'           => $type,
                'value'          => $value,
                'applies_to'     => $appliesTo,
                'applies_value'  => $val,
                'is_active'      => true,
                'notes'          => $notes,
                'created_by'     => Auth::id(),
            ]);
        }

        return back()->with('success', 'Discount rule(s) added successfully.');
    }

    public function destroyDiscount(StateDiscount $discount)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $discount->delete();
        return back()->with('success', 'Discount removed.');
    }
}
