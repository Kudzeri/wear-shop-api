<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'video_url',
        'description',
        'composition_care',
        'preference',
        'measurements',
        'price'
    ];

    protected $casts = [
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

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function syncImages(array $imagePaths): void
    {
        $this->images()->delete(); // Удаляем старые изображения
        foreach ($imagePaths as $path) {
            $this->images()->create(['image_path' => $path]);
        }
    }

}
