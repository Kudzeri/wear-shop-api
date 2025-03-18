<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="PickUpPoint",
 *   required={"id_pickup_point", "id_pickup_point_1c", "pick_up_point", "id_delivery_service", "id_delivery_service_1c"},
 *   @OA\Property(property="id_pickup_point", type="integer", description="Уникальный идентификатор ПВЗ на сайте"),
 *   @OA\Property(property="id_pickup_point_1c", type="string", description="Уникальный идентификатор ПВЗ в 1С"),
 *   @OA\Property(property="pick_up_point", type="string", description="Наименование пункта выдачи заказа"),
 *   @OA\Property(property="id_delivery_service", type="integer", description="Уникальный идентификатор службы доставки на сайте"),
 *   @OA\Property(property="id_delivery_service_1c", type="string", description="Уникальный идентификатор службы доставки в 1С")
 * )
 */

class PickUpPoint extends Model
{
    protected $fillable = [
        'id_pickup_point',            // число - Уникальный идентификатор на сайте
        'id_pickup_point_1c',         // строка - Идентификатор в 1С
        'pick_up_point',              // строка - Наименование ПВЗ
        'id_delivery_service',        // число - Идентификатор службы доставки на сайте
        'id_delivery_service_1c',     // строка - Идентификатор службы доставки в 1С
    ];

    public function deliveryService()
    {
        return $this->belongsTo(DeliveryService::class, 'id_delivery_service');
    }
}