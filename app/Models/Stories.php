<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
/**
 * @OA\Schema(
 *     schema="Story",
 *     title="Story",
 *     description="Модель сториса",
 *     @OA\Property(property="id", type="integer", example=1, description="ID сториса"),
 *     @OA\Property(property="image_url", type="string", example="https://example.com/story.jpg", description="Ссылка на изображение"),
 *     @OA\Property(
 *         property="products",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Product")
 *     ),
 * )
 */
class Stories extends Model
{
    protected $fillable = [
        'title',
        'image_url',  // добавить это поле
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'stories_products');
    }
}
