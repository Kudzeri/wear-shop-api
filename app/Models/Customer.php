<?php
/**
 * @OA\Schema(
 *     schema="Customer",
 *     type="object",
 *     title="Customer",
 *     required={"id_customer", "name", "email"},
 *     @OA\Property(
 *         property="id_customer",
 *         type="integer",
 *         example=1,
 *         description="Уникальный идентификатор покупателя на сайте"
 *     ),
 *     @OA\Property(
 *         property="id_customer_1c",
 *         type="string",
 *         example="1c-101",
 *         description="Идентификатор покупателя в 1С"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Иван",
 *         description="Имя покупателя"
 *     ),
 *     @OA\Property(
 *         property="surname",
 *         type="string",
 *         example="Иванов",
 *         description="Фамилия покупателя"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         example="ivanov@example.com",
 *         description="Email покупателя"
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         example="+70001234567",
 *         description="Телефон покупателя"
 *     )
 * )
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    // ...existing code...
}