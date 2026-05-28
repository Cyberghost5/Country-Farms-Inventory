<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'dispatch_id',
    'product_id',
    'quantity',
    'unit_price',
    'subtotal'
])]
class DispatchItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal'   => 'decimal:2',
        ];
    }

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
