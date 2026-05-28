<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'dispatch_id',
    'distributor_id',
    'invoice_number',
    'total_amount',
    'due_amount',
    'status',
    'due_date'
])]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'due_amount'   => 'decimal:2',
            'due_date'     => 'date',
        ];
    }

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
