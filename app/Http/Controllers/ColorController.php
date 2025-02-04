<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Colors",
 *     description="CRUD операции с цветами"
 * )
 */
class ColorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/colors",
     *     summary="Получение списка всех цветов",
     *     tags={"Colors"},
     *     @OA\Response(
     *         response=200,
     *         description="Список цветов",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Красный"),
     *                 @OA\Property(property="code", type="string", example="#FF0000")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Color::all());
    }

    /**
     * @OA\Post(
     *     path="/api/colors",
     *     summary="Создание нового цвета",
     *     tags={"Colors"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "code"},
     *             @OA\Property(property="name", type="string", example="Синий"),
     *             @OA\Property(property="code", type="string", example="#0000FF")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Цвет создан"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:7'
        ]);

        $color = Color::create($validated);
        return response()->json($color, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/colors/{id}",
     *     summary="Получение информации о конкретном цвете",
     *     tags={"Colors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID цвета",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Информация о цвете"),
     *     @OA\Response(response=404, description="Цвет не найден")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $color = Color::find($id);
        if (!$color) {
            return response()->json(['message' => 'Цвет не найден'], 404);
        }

        return response()->json($color);
    }

    /**
     * @OA\Put(
     *     path="/api/colors/{id}",
     *     summary="Обновление информации о цвете",
     *     tags={"Colors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID цвета",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Зеленый"),
     *             @OA\Property(property="code", type="string", example="#00FF00")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Цвет обновлен"),
     *     @OA\Response(response=404, description="Цвет не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $color = Color::find($id);
        if (!$color) {
            return response()->json(['message' => 'Цвет не найден'], 404);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'string|max:7'
        ]);

        $color->update($validated);
        return response()->json($color);
    }

    /**
     * @OA\Delete(
     *     path="/api/colors/{id}",
     *     summary="Удаление цвета",
     *     tags={"Colors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID цвета",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Цвет удален"),
     *     @OA\Response(response=404, description="Цвет не найден")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $color = Color::find($id);
        if (!$color) {
            return response()->json(['message' => 'Цвет не найден'], 404);
        }

        $color->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/colors/{id}/products",
     *     summary="Получение всех продуктов, связанных с цветом",
     *     tags={"Colors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID цвета",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список продуктов",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Футболка"),
     *                 @OA\Property(property="price", type="number", example=19.99)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Цвет не найден")
     * )
     */
    public function getProducts(int $id): JsonResponse
    {
        $color = Color::with('products')->find($id);
        if (!$color) {
            return response()->json(['message' => 'Цвет не найден'], 404);
        }

        return response()->json($color->products);
    }
}
