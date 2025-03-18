<?php
/**
 * @OA\Schema(
 *     schema="Promo",
 *     type="object",
 *     title="Promo",
 *     required={"id_promo", "discount_size"},
 *     @OA\Property(
 *         property="id_promo",
 *         type="integer",
 *         example=1,
 *         description="Уникальный идентификатор промокода"
 *     ),
 *     @OA\Property(
 *         property="discount_size",
 *         type="number",
 *         format="float",
 *         example=50,
 *         description="Размер скидки в рублях"
 *     ),
 *     @OA\Property(
 *         property="discount_percentage",
 *         type="number",
 *         format="float",
 *         example=10,
 *         description="Процент скидки"
 *     ),
 *     @OA\Property(
 *         property="discount_product",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Список id товаров, на которые распространяется скидка"
 *     ),
 *     @OA\Property(
 *         property="id_promo_1c",
 *         type="string",
 *         example="1c-501",
 *         description="Идентификатор промокода в 1С"
 *     ),
 *     @OA\Property(
 *         property="ready_promo",
 *         type="boolean",
 *         example=true,
 *         description="Флаг выгрузки промокода в 1С"
 *     )
 * )
 */
 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    // ...existing code...
}