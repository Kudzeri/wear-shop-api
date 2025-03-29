<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="DeliveryService",
 *   required={"id_delivery_service", "id_delivery_service_1c", "delivery_service"},
 *   @OA\Property(property="id_delivery_service", type="integer", description="Уникальный идентификатор на сайте"),
 *   @OA\Property(property="id_delivery_service_1c", type="string", description="Уникальный идентификатор в 1С"),
 *   @OA\Property(property="delivery_service", type="string", description="Название службы доставки")
 * )
 */

class DeliveryService extends Model
{
    protected $fillable = [
        'id_delivery_service',        // число - Уникальный идентификатор на сайте
        'id_delivery_service_1c',     // строка - Идентификатор в 1С
        'delivery_service',           // строка - Название службы
    ];
}