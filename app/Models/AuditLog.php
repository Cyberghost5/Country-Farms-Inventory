<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'user_name',
    'action',
    'item_details',
    'reason'
])]
class AuditLog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'item_details' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
