<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'code',
        'phone',
        'address',
        'group',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
