<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'distributor_id', 'price'])]
class DistributorPricing extends Model
{
    /**
     * Explicit table name: migration created 'distributor_pricing'
     * while Eloquent would expect 'distributor_pricings'.
     */
    protected $table = 'distributor_pricing';
    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}
