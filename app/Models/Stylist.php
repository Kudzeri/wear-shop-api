<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stylist extends Model
{
    protected $fillable = ['image_url'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
