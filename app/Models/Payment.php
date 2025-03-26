<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     title="Payment",
 *     required={"user_id", "order_id", "amount", "currency", "status", "payment_method", "transaction_id"},
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=123),
 *     @OA\Property(property="amount", type="number", format="float", example=1500.00),
 *     @OA\Property(property="currency", type="string", example="RUB"),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="payment_method", type="string", example="bank_card"),
 *     @OA\Property(property="transaction_id", type="string", example="2a3b5c7d-1234-5678-9abc-def012345678")
 * )
 */
class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id'
    ];

    /**
     * Связь с пользователем.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с заказом.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
