<?php

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     required={"product_id", "size_id", "quantity", "price"},
 *     @OA\Property(property="product_id", type="integer", description="ID продукта"),
 *     @OA\Property(property="size_id", type="integer", description="ID размера продукта"),
 *     @OA\Property(property="quantity", type="integer", description="Количество товара"),
 *     @OA\Property(property="price", type="number", format="float", description="Цена товара")
 * )
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'size_id', 'quantity', 'price'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }
}
