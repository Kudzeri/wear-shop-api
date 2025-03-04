<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stories as Story;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Stories", description="API для работы со сторисами")
 */
class StoriesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/stories",
     *     summary="Получить список сторисов с пагинацией",
     *     tags={"Stories"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список сторисов",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Story"))
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Story::with('products')->paginate(10));
    }

    /**
     * @OA\Post(
     *     path="/api/stories",
     *     summary="Создать новый сторис",
     *     tags={"Stories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"image_url"},
     *             @OA\Property(property="image_url", type="string", example="https://example.com/story.jpg")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Сторис создан")
     * )
     */
    public function store(Request $request)
    {
        $story = Story::create($request->validate(['image_url' => 'required|string']));
        return response()->json($story, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/stories/{id}",
     *     summary="Получить конкретный сторис",
     *     tags={"Stories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID сториса",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о сторисе",
     *         @OA\JsonContent(ref="#/components/schemas/Story")
     *     )
     * )
     */
    public function show($id)
    {
        $story = Story::with('products')->findOrFail($id);
        return response()->json($story);
    }

    /**
     * @OA\Delete(
     *     path="/api/stories/{id}",
     *     summary="Удалить сторис",
     *     tags={"Stories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID сториса",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Сторис удален")
     * )
     */
    public function destroy($id)
    {
        $story = Story::findOrFail($id);
        $story->delete();
        return response()->json(['message' => 'Story deleted']);
    }

    /**
     * @OA\Post(
     *     path="/api/stories/{story}/products",
     *     summary="Добавить товар в сторис",
     *     tags={"Stories"},
     *     @OA\Parameter(
     *         name="story",
     *         in="path",
     *         required=true,
     *         description="ID сториса",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Товар добавлен")
     * )
     */
    public function addProduct(Story $story, Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $story->products()->attach($product);
        return response()->json(['message' => 'Product added to story']);
    }

    /**
     * @OA\Delete(
     *     path="/api/stories/{story}/products",
     *     summary="Удалить товар из сториса",
     *     tags={"Stories"},
     *     @OA\Parameter(
     *         name="story",
     *         in="path",
     *         required=true,
     *         description="ID сториса",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Товар удален")
     * )
     */
    public function removeProduct(Story $story, Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $story->products()->detach($product);
        return response()->json(['message' => 'Product removed from story']);
    }
}
