<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\StateDiscount;
use App\Models\StatePricing;

#[Fillable(['name', 'sku', 'category', 'size_volume', 'packaging_type', 'unit', 'base_price', 'description', 'is_active', 'created_by'])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    const CATEGORIES = ['yoghurt', 'drink', 'snack', 'packaging', 'others'];
    const UNITS       = ['piece', 'pack', 'carton', 'litre', 'sachet'];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active'  => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statePricing()
    {
        return $this->hasMany(StatePricing::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductionBatch::class);
    }

    public function verifiedStock(): int
    {
        $produced = (int) $this->batches()->verified()->sum('quantity');
        $dispatched = (int) \App\Models\DispatchItem::where('product_id', $this->id)
            ->whereHas('dispatch', function ($q) {
                $q->whereIn('status', ['dispatched', 'received']);
            })
            ->sum('quantity');
        return max(0, $produced - $dispatched);
    }

    // ── Helpers ───────────────────────────────────────────────────

    /** Generate a SKU from category + name if not provided */
    public static function generateSku(string $category, string $name): string
    {
        $prefix = strtoupper(substr($category, 0, 3));
        $slug   = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $name));
        $slug   = substr($slug, 0, 6);
        $suffix = strtoupper(substr(uniqid(), -4));
        return "{$prefix}-{$slug}-{$suffix}";
    }

    public function priceForState(string $state): float
    {
        $custom = StatePricing::where('product_id', $this->id)
            ->where('state', $state)
            ->first();
        return $custom ? (float) $custom->price : (float) $this->base_price;
    }

    public function calculatedPriceForState(string $state): float
    {
        $price = $this->priceForState($state);

        $discounts = StateDiscount::where('state', $state)
            ->where('is_active', true)
            ->get();

        foreach ($discounts as $discount) {
            $applies = false;
            if ($discount->applies_to === 'all') {
                $applies = true;
            } elseif ($discount->applies_to === 'category' && $discount->applies_value === $this->category) {
                $applies = true;
            } elseif ($discount->applies_to === 'product' && (int)$discount->applies_value === $this->id) {
                $applies = true;
            }

            if ($applies) {
                if ($discount->type === 'percentage') {
                    $price -= $price * ($discount->value / 100);
                } elseif ($discount->type === 'fixed') {
                    $price -= $discount->value;
                }
            }
        }

        return max(0.0, (float) $price);
    }

    /** Price for a specific distributor (falls back to base_price) */
    public function priceForDistributor(int $distributorId, ?string $state = null): float
    {
        $distributor = User::find($distributorId);
        if ($distributor) {
            $state = $state ?: ($distributor->operatingAreas()->first()?->state ?? $distributor->state);
            if ($state) {
                return $this->priceForState($state);
            }
        }
        return (float) $this->base_price;
    }

    /** Calculate net price for a specific distributor, applying custom pricing and active discounts */
    public function calculatedPriceForDistributor(int $distributorId, ?string $state = null): float
    {
        $distributor = User::find($distributorId);
        if (!$distributor) {
            return max(0.0, (float) $this->base_price);
        }

        $state = $state ?: ($distributor->operatingAreas()->first()?->state ?? $distributor->state);
        if (!$state) {
            return max(0.0, (float) $this->priceForDistributor($distributorId, $state));
        }

        return $this->calculatedPriceForState($state);
    }

    public function getCategoryLabelAttribute(): string
    {
        return ucfirst($this->category);
    }
}
