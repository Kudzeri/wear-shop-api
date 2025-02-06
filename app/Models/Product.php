<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Продукт",
 *     description="Модель продукта",
 *     required={"id", "name", "category_id", "description", "price"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Футболка 'Rich as'"),
 *     @OA\Property(property="category_id", type="integer", example=18),
 *     @OA\Property(property="description", type="string", example="Описание продукта"),
 *     @OA\Property(property="video_url", type="string", nullable=true, example="https://example.com/video.mp4"),
 *     @OA\Property(property="price", type="number", format="float", example=5760),
 *     @OA\Property(property="image_urls", type="array", @OA\Items(type="string", example="https://example.com/image1.jpg")),
 *     @OA\Property(property="preference", type="object",
 *         @OA\Property(property="S", type="object",
 *             @OA\Property(property="длина", type="integer", example=60),
 *             @OA\Property(property="обхват_груди", type="integer", example=90)
 *         )
 *     ),
 *     @OA\Property(property="measurements", type="object",
 *         @OA\Property(property="S", type="object",
 *             @OA\Property(property="длина", type="integer", example=60),
 *             @OA\Property(property="обхват_груди", type="integer", example=90)
 *         )
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-06T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-06T12:34:56Z")
 * )
 */

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

    public function syncImagesAdm(?array $imagePaths): void
    {
        if ($imagePaths === null) {
            return;
        }

        $this->images()->delete(); // Удаляем старые изображения

        foreach ($imagePaths as $path) {
            $this->images()->create(['image_path' => 'products/' . $path]);
        }
    }

    public function setVideoFileAttribute($file)
    {
        if ($file) {
            $this->attributes['video_url'] = Storage::url($file);
        }
    }

    public function getVideoUrlAttribute($value)
    {
        return $value ? asset($value) : null;
    }

    /**
     * Получение самых популярных товаров по количеству добавлений в избранное
     *
     * @param int $limit Количество возвращаемых товаров (по умолчанию 10)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPopularProducts(int $limit = 10)
    {
        return self::withCount('wishlistedBy')
            ->orderByDesc('wishlisted_by_count')
            ->limit($limit)
            ->get();
    }

}
