<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyLevel;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Loyalty",
 *     description="Программа лояльности"
 * )
 */
class LoyaltyController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/loyalty/use-points",
     *     summary="Списать баллы у пользователя",
     *     tags={"Loyalty"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"points"},
     *             @OA\Property(property="points", type="integer", example=100, description="Количество списываемых баллов")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Баллы успешно списаны",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Баллы успешно списаны"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка списания баллов",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Недостаточно баллов."))
     *     )
     * )
     */
    public function usePoints(Request $request, LoyaltyService $loyaltyService)
    {
        $request->validate([
            'points' => 'required|integer|min:1'
        ]);

        try {
            $loyaltyService->redeemPoints(Auth::user(), $request->points);
            return response()->json(['message' => 'Баллы успешно списаны']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/loyalty/points",
     *     summary="Получить общее количество баллов пользователя",
     *     tags={"Loyalty"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Общее количество баллов",
     *         @OA\JsonContent(@OA\Property(property="total_points", type="integer", example=500))
     *     )
     * )
     */
    public function getUserPoints(LoyaltyService $loyaltyService)
    {
        $user = Auth::user();
        $points = $loyaltyService->getTotalPoints($user);
        return response()->json(['total_points' => $points]);
    }

    /**
     * @OA\Get(
     *     path="/api/loyalty/level",
     *     summary="Получить уровень лояльности пользователя",
     *     tags={"Loyalty"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Текущий уровень лояльности",
     *         @OA\JsonContent(@OA\Property(property="level", type="string", example="Gold"))
     *     )
     * )
     */
    public function getUserLevel(LoyaltyService $loyaltyService)
    {
        $user = Auth::user();
        $level = $loyaltyService->getUserLevel($user);
        return response()->json(['level' => $level ? $level->name : 'Нет уровня']);
    }
}
