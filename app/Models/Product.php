<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'category_id',
        'image_urls',
        'video_url',
        'description',
        'composition_care',
        'preference',
        'measurements',
    ];

    protected $casts = [
        'image_urls' => 'array',
        'preference' => 'array',
        'measurements' => 'array',
    ];

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'color_products');
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_size');
    }

}
