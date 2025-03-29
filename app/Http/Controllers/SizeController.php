<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Sizes", description="Управление размерами товаров")
 */
class SizeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/sizes",
     *     summary="Получение списка размеров",
     *     tags={"Sizes"},
     *     @OA\Response(
     *         response=200,
     *         description="Список размеров",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="M"),
     *                 @OA\Property(property="slug", type="string", example="m")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Size::all());
    }

    /**
     * @OA\Post(
     *     path="/api/sizes",
     *     summary="Создание нового размера",
     *     tags={"Sizes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug"},
     *             @OA\Property(property="name", type="string", example="M"),
     *             @OA\Property(property="slug", type="string", example="m")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Размер создан"),
     *     @OA\Response(response=400, description="Ошибка валидации")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:sizes',
            'slug' => 'required|string|unique:sizes',
        ]);

        $size = Size::create($validated);

        return response()->json($size, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/sizes/{slug}/products",
     *     summary="Получение всех товаров по размеру",
     *     tags={"Sizes"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug размера",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список товаров",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Футболка"),
     *                 @OA\Property(property="price", type="number", example=19.99)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Размер не найден")
     * )
     */
    public function getProductsBySize(string $slug): JsonResponse
    {
        $size = Size::where('slug', $slug)->with('products')->first();

        if (!$size) {
            return response()->json(['message' => 'Размер не найден'], 404);
        }

        return response()->json($size->products);
    }

    /**
     * @OA\Delete(
     *     path="/api/sizes/{id}",
     *     summary="Удаление размера",
     *     tags={"Sizes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID размера",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Размер удален"),
     *     @OA\Response(response=404, description="Размер не найден")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json(['message' => 'Размер не найден'], 404);
        }

        $size->delete();

        return response()->json(null, 204);
    }
}
