<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['distributor_id', 'order_number', 'status', 'total_amount', 'remarks', 'processed_by', 'processed_at'])]
class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
