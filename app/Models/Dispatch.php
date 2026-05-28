<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'distributor_id',
    'dispatch_number',
    'status',
    'dispatched_by',
    'dispatched_at',
    'remarks',
    'total_amount'
])]
class Dispatch extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'dispatched_at' => 'datetime',
            'total_amount'  => 'decimal:2',
        ];
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function items()
    {
        return $this->hasMany(DispatchItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
