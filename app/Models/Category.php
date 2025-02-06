<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Категория",
 *     description="Модель категории",
 *     required={"id", "slug", "title"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="slug", type="string", example="electronics"),
 *     @OA\Property(property="title", type="string", example="Электроника"),
 *     @OA\Property(property="category_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="image", type="string", nullable=true, example="https://example.com/category.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-06T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-06T12:34:56Z"),
 *     @OA\Property(property="children", type="array", @OA\Items(ref="#/components/schemas/Category")),
 *     @OA\Property(property="products", type="array", @OA\Items(ref="#/components/schemas/Product"))
 * )
 */

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['slug', 'title', 'category_id', 'image'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
