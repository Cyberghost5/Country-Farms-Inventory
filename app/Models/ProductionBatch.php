<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'product_id',
    'quantity',
    'batch_number',
    'production_date',
    'expiry_date',
    'remarks',
    'is_verified',
    'verified_by',
    'verified_at',
    'uploaded_by'
])]
class ProductionBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'is_verified'     => 'boolean',
            'production_date' => 'date',
            'expiry_date'     => 'date',
            'verified_at'     => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_verified', false);
    }
}
