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

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order",
 *     required={"id_order", "date_order", "order_amount"},
 *     @OA\Property(
 *         property="id_order",
 *         type="integer",
 *         example=1,
 *         description="Уникальный идентификатор заказа на сайте"
 *     ),
 *     @OA\Property(
 *         property="date_order",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-01 12:30:00",
 *         description="Дата создания заказа"
 *     ),
 *     @OA\Property(
 *         property="number_order_1c",
 *         type="string",
 *         example="1001",
 *         description="Номер заказа в 1С"
 *     ),
 *     @OA\Property(
 *         property="date_order_1c",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-01 12:30:00",
 *         description="Дата заказа в 1С"
 *     ),
 *     @OA\Property(
 *         property="order_amount",
 *         type="number",
 *         format="float",
 *         example=250.75,
 *         description="Сумма заказа"
 *     )
 * )
 */

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'address_id', 'total_price', 'status', 'delivery', 'payment_id',
        'delivery_service_id',      // связь с DeliveryService (id службы доставки на сайте)
        'delivery_service_1c',      // идентификатор службы доставки в 1С
        'pickup_point_id',          // связь с PickUpPoint (id ПВЗ на сайте)
        'pickup_point_1c',          // идентификатор ПВЗ в 1С
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

    // Отношение с моделью DeliveryService
    public function deliveryService()
    {
        return $this->belongsTo(\App\Models\DeliveryService::class, 'delivery_service_id');
    }

    // Отношение с моделью PickUpPoint
    public function pickupPoint()
    {
        return $this->belongsTo(\App\Models\PickUpPoint::class, 'pickup_point_id');
    }
    
    // Обновленная бизнес-логика заказа с учетом нового функционала
    public function updateOrderLogic()
    {
        // ...existing logic...
        
        if ($this->delivery_service_id) {
            // Логика обработки службы доставки:
            // Например, устанавливаем статус "processing" и отправляем уведомление
            $this->status = 'processing';
            // ...дополнительная логика (например, уведомление через email)...
        }
        
        if ($this->pickup_point_id) {
            // Логика обработки пункта выдачи:
            // Например, отмечаем, что уведомление о пункте выдачи было отправлено
            // $this->pickup_notification_sent = true;
            // ...дополнительная логика...
        }
        
        // Сохраняем изменения заказа
        $this->save();
        
        // ...existing logic...
    }

}
