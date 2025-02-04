<?php

namespace App\Services;

use App\Models\LoyaltyLevel;
use App\Models\LoyaltyPoint;
use App\Models\User;

class LoyaltyService
{
    public function addPoints(User $user, int $points, string $reason = null)
    {
        $user->loyaltyPoints()->create([
            'points' => $points,
            'reason' => $reason
        ]);
    }

    public function getTotalPoints(User $user): int
    {
        return $user->loyaltyPoints()->sum('points');
    }

    public function redeemPoints(User $user, int $points)
    {
        $totalPoints = $this->getTotalPoints($user);

        if ($totalPoints < $points) {
            throw new \Exception("Недостаточно баллов.");
        }

        // Списываем баллы
        $user->loyaltyPoints()->create([
            'points' => -$points,
            'reason' => "Списание баллов"
        ]);
    }

    public function getUserLevel(User $user)
    {
        $points = $this->getTotalPoints($user);
        return LoyaltyLevel::where('min_points', '<=', $points)->orderByDesc('min_points')->first();
    }
}
