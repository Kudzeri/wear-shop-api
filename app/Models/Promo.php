<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Promo",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="SIVENO10"),
 *     @OA\Property(property="discount", type="integer", example=10),
 *     @OA\Property(property="expires_at", type="string", format="date", example="2025-12-31"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Promo extends Model
{
    protected $fillable = ['code', 'discount', 'expires_at', 'usage_count'];
}
