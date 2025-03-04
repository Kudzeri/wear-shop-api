<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stylist;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Stylists", description="API для работы с выборами стилистов")
 */
class StylistController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/stylists",
     *     summary="Получить список стилистов с пагинацией",
     *     tags={"Stylists"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список стилистов",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Stylist"))
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Stylist::with('products')->paginate(10));
    }

    /**
     * @OA\Post(
     *     path="/api/stylists",
     *     summary="Создать нового стилиста",
     *     tags={"Stylists"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"image_url"},
     *             @OA\Property(property="image_url", type="string", example="https://example.com/stylist.jpg")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Стилист создан")
     * )
     */
    public function store(Request $request)
    {
        $stylist = Stylist::create($request->validate(['image_url' => 'required|string']));
        return response()->json($stylist, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/stylists/{id}",
     *     summary="Получить конкретного стилиста",
     *     tags={"Stylists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID стилиста",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о стилисте",
     *         @OA\JsonContent(ref="#/components/schemas/Stylist")
     *     )
     * )
     */
    public function show($id)
    {
        $stylist = Stylist::with('products')->findOrFail($id);
        return response()->json($stylist);
    }

    /**
     * @OA\Delete(
     *     path="/api/stylists/{id}",
     *     summary="Удалить стилиста",
     *     tags={"Stylists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID стилиста",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Стилист удален")
     * )
     */
    public function destroy($id)
    {
        $stylist = Stylist::findOrFail($id);
        $stylist->delete();
        return response()->json(['message' => 'Stylist deleted']);
    }

    /**
     * @OA\Post(
     *     path="/api/stylists/{stylist}/products",
     *     summary="Добавить товар к стилисту",
     *     tags={"Stylists"},
     *     @OA\Parameter(
     *         name="stylist",
     *         in="path",
     *         required=true,
     *         description="ID стилиста",
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
    public function addProduct(Stylist $stylist, Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $stylist->products()->attach($product);
        return response()->json(['message' => 'Product added to stylist']);
    }

    /**
     * @OA\Delete(
     *     path="/api/stylists/{stylist}/products",
     *     summary="Удалить товар у стилиста",
     *     tags={"Stylists"},
     *     @OA\Parameter(
     *         name="stylist",
     *         in="path",
     *         required=true,
     *         description="ID стилиста",
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
    public function removeProduct(Stylist $stylist, Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $stylist->products()->detach($product);
        return response()->json(['message' => 'Product removed from stylist']);
    }
}

