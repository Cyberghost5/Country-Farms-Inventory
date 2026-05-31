<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'state', 'lga'])]
class UserOperatingArea extends Model
{
    protected $table = 'user_operating_areas';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
