<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
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
     *     @OA\Response(response=200, description="Баллы успешно списаны"),
     *     @OA\Response(response=400, description="Недостаточно баллов"),
     *     @OA\Response(response=401, description="Неавторизованный доступ")
     * )
     */
    public function usePoints(Request $request, LoyaltyService $loyaltyService): JsonResponse
    {
        $request->validate(['points' => 'required|integer|min:1']);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Неавторизованный доступ'], 401);
        }

        if ($loyaltyService->redeemPoints($user, $request->points)) {
            return response()->json(['message' => 'Баллы успешно списаны']);
        }

        return response()->json(['error' => 'Недостаточно баллов'], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/loyalty/earn-points",
     *     summary="Начислить баллы пользователю",
     *     tags={"Loyalty"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"points"},
     *             @OA\Property(property="points", type="integer", example=50, description="Количество начисляемых баллов")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Баллы успешно начислены"),
     *     @OA\Response(response=401, description="Неавторизованный доступ")
     * )
     */
    public function earnPoints(Request $request, LoyaltyService $loyaltyService): JsonResponse
    {
        $request->validate(['points' => 'required|integer|min:1']);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Неавторизованный доступ'], 401);
        }

        $loyaltyService->addPoints($user, $request->points);
        return response()->json(['message' => 'Баллы успешно начислены']);
    }

    /**
     * @OA\Get(
     *     path="/api/loyalty/points",
     *     summary="Получить общее количество баллов пользователя",
     *     tags={"Loyalty"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(response=200, description="Общее количество баллов")
     * )
     */
    public function getUserPoints(LoyaltyService $loyaltyService): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Неавторизованный доступ'], 401);
        }

        return response()->json(['total_points' => $loyaltyService->getTotalPoints($user)]);
    }

    /**
     * @OA\Get(
     *     path="/api/loyalty/points-history",
     *     summary="Получить историю операций с баллами",
     *     tags={"Loyalty"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(response=200, description="История операций с баллами")
     * )
     */
    public function getPointsHistory(LoyaltyService $loyaltyService): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Неавторизованный доступ'], 401);
        }

        return response()->json(['history' => $loyaltyService->getPointsHistory($user)]);
    }

    /**
     * @OA\Get(
     *     path="/api/loyalty/level",
     *     summary="Получить уровень лояльности пользователя",
     *     tags={"Loyalty"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(response=200, description="Текущий уровень лояльности")
     * )
     */
    public function getUserLevel(LoyaltyService $loyaltyService): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Неавторизованный доступ'], 401);
        }

        return response()->json(['level' => $loyaltyService->getUserLevel($user)->name ?? 'Нет уровня']);
    }

    /**
     * @OA\Post(
     *     path="/api/loyalty/apply-discount",
     *     summary="Применить скидку за баллы и уровень",
     *     tags={"Loyalty"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"total_amount"},
     *             @OA\Property(property="total_amount", type="integer", example=1000, description="Сумма заказа")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Скидка успешно применена")
     * )
     */
    public function applyDiscount(Request $request, LoyaltyService $loyaltyService): JsonResponse
    {
        $request->validate(['total_amount' => 'required|integer|min:1']);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Неавторизованный доступ'], 401);
        }

        return response()->json($loyaltyService->applyDiscount($user, $request->total_amount));
    }

}
