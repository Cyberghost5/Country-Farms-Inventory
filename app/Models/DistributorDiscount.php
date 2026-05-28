<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['distributor_id', 'type', 'value', 'applies_to', 'applies_value', 'is_active', 'notes', 'created_by'])]
class DistributorDiscount extends Model
{
    protected function casts(): array
    {
        return [
            'value'     => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'applies_value');
    }

    /** Human-readable discount label */
    public function getLabelAttribute(): string
    {
        $val = $this->type === 'percentage'
            ? "{$this->value}% off"
            : "₦" . number_format($this->value, 2) . " off";

        return match ($this->applies_to) {
            'all'      => "{$val} on all products",
            'category' => "{$val} on {$this->applies_value}",
            'product'  => "{$val} on product " . ($this->product ? $this->product->name : "#{$this->applies_value}"),
            default    => $val,
        };
    }
}
