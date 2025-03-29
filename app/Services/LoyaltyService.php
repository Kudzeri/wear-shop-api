<?php
namespace App\Services;

use App\Models\User;
use App\Models\LoyaltyLevel;
use App\Models\LoyaltyTransaction;

class LoyaltyService
{
    public function addPoints(User $user, int $points): void
    {
        $user->increment('loyalty_points', $points);
        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'points' => $points,
            'type' => 'earn',
            'description' => 'Начисление баллов'
        ]);

        $this->updateUserLevel($user);
    }

    public function redeemPoints(User $user, int $points): bool
    {
        if ($user->loyalty_points < $points) {
            return false;
        }

        $user->decrement('loyalty_points', $points);
        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'points' => -$points,
            'type' => 'redeem',
            'description' => 'Списание баллов'
        ]);

        return true;
    }

    public function getTotalPoints(User $user): int
    {
        return $user->loyaltyPoints()->sum('points');
    }

    public function getUserLevel(User $user)
    {
        return $user->loyaltyLevel;
    }

    public function getPointsHistory(User $user)
    {
        return $user->transactions()->orderByDesc('created_at')->get();
    }

    public function applyDiscount(User $user, int $totalAmount): array
    {
        $loyaltyLevel = $user->loyaltyLevel;

        // Проверяем, есть ли уровень у пользователя
        $discountPercentage = $loyaltyLevel ? $loyaltyLevel->discount_percentage : 0;
        $discountAmount = ($totalAmount * $discountPercentage) / 100;

        // Получаем сумму баллов пользователя
        $userPoints = $user->loyaltyPoints()->sum('points');

        // Определяем количество баллов для списания
        $pointsToRedeem = min($userPoints, $totalAmount - $discountAmount);
        $finalAmount = $totalAmount - $discountAmount - $pointsToRedeem;

        if ($pointsToRedeem > 0) {
            $this->redeemPoints($user, $pointsToRedeem);
        }

        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'points' => -$pointsToRedeem,
            'type' => 'discount',
            'description' => "Скидка {$discountPercentage}% + {$pointsToRedeem} баллов"
        ]);

        return [
            'original_amount' => $totalAmount,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'points_redeemed' => $pointsToRedeem,
            'final_amount' => max($finalAmount, 0)
        ];
    }


    private function updateUserLevel(User $user): void
    {
        $newLevel = LoyaltyLevel::where('min_points', '<=', $user->loyalty_points)
            ->orderByDesc('min_points')
            ->first();

        if ($newLevel && $user->loyalty_level_id !== $newLevel->id) {
            $user->update(['loyalty_level_id' => $newLevel->id]);
        }
    }
}
