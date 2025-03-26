<?php
/**
 * @OA\Schema(
 *     schema="Address",
 *     type="object",
 *     title="Адрес",
 *     description="Модель адреса пользователя",
 *     required={"id", "state", "city", "street", "house", "postal_code"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="is_primary", type="boolean", example=true, description="Является ли адрес основным"),
 *     @OA\Property(property="state", type="string", example="Алматинская область"),
 *     @OA\Property(property="city", type="string", example="Алматы"),
 *     @OA\Property(property="street", type="string", example="Абая"),
 *     @OA\Property(property="house", type="string", example="12"),
 *     @OA\Property(property="apartment", type="string", nullable=true, example="45"),
 *     @OA\Property(property="postal_code", type="string", example="050000"),
 *     @OA\Property(property="full_address", type="string", example="Алматы, ул. Абая, д. 12, кв. 45")
 * )
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Address extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_primary',
        'state',
        'city',
        'street',
        'house',
        'postal_code',
        'apartment'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'address_users')->withTimestamps();
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->city,
            $this->street ? "ул. {$this->street}" : null,
            $this->house ? "д. {$this->house}" : null,
            $this->apartment ? "кв. {$this->apartment}" : null
        ])->filter()->join(', ');
    }
}
