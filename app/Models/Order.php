<?php

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     required={"id", "user_id", "address_id", "total_price", "status"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="address_id", type="integer"),
 *     @OA\Property(property="total_price", type="number", format="float"),
 *     @OA\Property(property="status", type="string", enum={"pending", "processed", "shipped", "delivered", "cancelled", "completed"}),
 *     @OA\Property(property="delivery", type="string"),
 *     @OA\Property(property="payment_id", type="integer", nullable=true),
 *     @OA\Property(property="delivery_service_id", type="integer", nullable=true),
 *     @OA\Property(property="delivery_service_1c", type="string", nullable=true),
 *     @OA\Property(property="pickup_point_id", type="integer", nullable=true),
 *     @OA\Property(property="pickup_point_1c", type="string", nullable=true),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItem")),
 *     @OA\Property(property="address", ref="#/components/schemas/Address")
 * )
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;



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
