<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
/**
 * @OA\Schema(
 *     schema="Color",
 *     type="object",
 *     title="Цвет",
 *     description="Модель цвета",
 *     required={"id", "name", "code"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Красный"),
 *     @OA\Property(property="code", type="string", example="#FF0000", description="HEX-код цвета")
 * )
 */

class Color extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'color_products');
    }
}
