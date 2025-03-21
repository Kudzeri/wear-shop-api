<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Модель продукта",
 *     required={"id_product", "id_product_1c", "title", "article", "description", "unit", "image_urls", "price", "weight", "length", "width", "height", "ready"},
 *     @OA\Property(property="id_product", type="integer", example=1, description="Уникальный идентификатор товара на сайте"),
 *     @OA\Property(property="id_product_1c", type="string", example="1c-001", description="Уникальный идентификатор товара в 1С"),
 *     @OA\Property(property="title", type="string", example="Футболка", description="Наименование товара"),
 *     @OA\Property(property="article", type="string", example="ABC123", description="Артикул товара"),
 *     @OA\Property(property="description", type="string", example="Полное описание товара", description="Описание товара"),
 *     @OA\Property(property="unit", type="string", example="шт", description="Единица измерения"),
 *     @OA\Property(property="image_urls", type="array", @OA\Items(type="string", example="https://example.com/image.jpg"), description="Ссылки на изображения"),
 *     @OA\Property(property="price", type="number", format="float", example=199.99, description="Цена продажи"),
 *     @OA\Property(property="weight", type="number", format="float", example=0.5, description="Вес в килограммах"),
 *     @OA\Property(property="length", type="number", format="float", example=30, description="Длина в сантиметрах"),
 *     @OA\Property(property="width", type="number", format="float", example=20, description="Ширина в сантиметрах"),
 *     @OA\Property(property="height", type="number", format="float", example=10, description="Высота в сантиметрах"),
 *     @OA\Property(property="ready", type="boolean", example=true, description="Флаг выгрузки/изменения карты товара в 1С")
 * )
 */

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'description',
        //'images',
        'video_file',
        'composition_care',
        'preference',
        'measurements',
        'price',
        'is_discount',
        'discount_percentage',
    ];

    protected $casts = [
        'preference' => 'array',
        'measurements' => 'array',
        'is_discount' => 'boolean',
        'discount_percentage' => 'decimal:2',
    ];

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'color_products');
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_size');
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists', 'product_id', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    // Обновлённый метод синхронизации изображений
    public function syncImages(array $imagePaths): void
    {
        $this->images()->delete(); // удаляем старые изображения
        foreach ($imagePaths as $path) {
            $this->images()->create(['image_path' => $path]);
        }
    }

    // Если метод syncImagesAdm использовался ранее, его можно удалить или оставить для обратной совместимости
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
            ->take($limit)
            ->get();
    }


    public function getDiscountedPrice(): float
    {
        if ($this->is_discount && $this->discount_percentage > 0) {
            return round($this->price * (1 - $this->discount_percentage / 100), 2);
        }

        return $this->price;
    }

    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(Stories::class, 'stories_products');
    }

    public function stylists(): BelongsToMany
    {
        return $this->belongsToMany(Stylist::class, 'stylist_products');
    }

    public function getImagePathAttribute($value) {
        // Если значение может быть пустым, можно добавить проверку
        return 'https://siveno.shop/storage/' . $value;
    }
}
