<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Subscriber",
 *     description="Подписчик рассылки",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Иван Иванов"),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-25T14:15:22Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-25T14:15:22Z")
 * )
 */
class Subscriber extends Model
{
    protected $fillable = ['name','email'];
}
