<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Stories extends Model
{
    protected $fillable = ['image_url'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
