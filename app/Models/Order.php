<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
/**
 * @OA\Components(
 *     @OA\Schema(
 *         schema="Order",
 *         type="object",
 *         required={"id", "user_id", "address_id", "total_price", "status"},
 *         @OA\Property(property="id", type="integer", description="ID заказа"),
 *         @OA\Property(property="user_id", type="integer", description="ID пользователя"),
 *         @OA\Property(property="address_id", type="integer", description="ID адреса доставки"),
 *         @OA\Property(property="total_price", type="number", format="float", description="Общая сумма заказа"),
 *         @OA\Property(property="status", type="string", enum={"pending", "processed", "shipped", "delivered", "cancelled"}, description="Статус заказа"),
 *         @OA\Property(
 *             property="items",
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/OrderItem")
 *         ),
 *         @OA\Property(
 *             property="address",
 *             type="object",
 *             ref="#/components/schemas/Address"
 *         )
 *     ),
 *
 *     @OA\Schema(
 *         schema="OrderItem",
 *         type="object",
 *         required={"product_id", "size_id", "quantity", "price"},
 *         @OA\Property(property="product_id", type="integer", description="ID продукта"),
 *         @OA\Property(property="size_id", type="integer", description="ID размера продукта"),
 *         @OA\Property(property="quantity", type="integer", description="Количество товара"),
 *         @OA\Property(property="price", type="number", format="float", description="Цена товара")
 *     )
 * )
 */

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'address_id', 'total_price', 'status'
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

}
