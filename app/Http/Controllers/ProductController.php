<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Получить список всех продуктов",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Список всех продуктов",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *     )
     * )
     */

    public function index(): JsonResponse
    {
        $products = Product::with('images')->get();
        return response()->json($products, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Создать новый продукт",
     *     security={{"bearerAuth": {}}},
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "category_id", "description"},
     *             @OA\Property(property="name", type="string", example="Футболка 'Rich as'"),
     *             @OA\Property(property="category_id", type="integer", example=18),
     *             @OA\Property(property="description", type="string", example="Описание продукта"),
     *             @OA\Property(property="image_files", type="array", @OA\Items(type="string", format="binary")),
     *             @OA\Property(property="video_file", type="string", format="binary"),
     *             @OA\Property(property="preference", type="object"),
     *             @OA\Property(property="measurements", type="object")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Продукт успешно создан"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */

    public function store(Request $request): JsonResponse
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        try {
            $data = $request->validate([
                'name' => 'required|string',
                'category_id' => 'required|integer|exists:categories,id',
                'description' => 'required|string',
                'image_files' => 'array',
                'image_files.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'video_url' => 'nullable|string|url',
                'video_file' => 'nullable|file|mimes:mp4,mov,avi|max:10240',
                'preference' => 'nullable|array',
                'measurements' => 'nullable|array',
            ]);

            $product = Product::create($data);

            // Загрузка изображений
            if ($request->hasFile('image_files')) {
                $uploadedImages = [];
                foreach ($request->file('image_files') as $image) {
                    $path = $image->store('products', 'public');
                    $uploadedImages[] = Storage::url($path);
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                    ]);
                }
            }

            // Загрузка видео
            if ($request->hasFile('video_file')) {
                $videoPath = $request->file('video_file')->store('products/videos', 'public');
                $product->update(['video_url' => Storage::url($videoPath)]);
            }

            return response()->json($product->load('images'), 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Обновить информацию о продукте",
     *     security={{"bearerAuth": {}}},
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID продукта", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Обновленный продукт"),
     *             @OA\Property(property="category_id", type="integer", example=18),
     *             @OA\Property(property="description", type="string", example="Новое описание"),
     *             @OA\Property(property="image_files", type="array", @OA\Items(type="string", format="binary")),
     *             @OA\Property(property="video_file", type="string", format="binary"),
     *             @OA\Property(property="preference", type="object"),
     *             @OA\Property(property="measurements", type="object")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Продукт обновлен"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Продукт не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */

    public function update(Request $request, int $id): JsonResponse
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|string',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'description' => 'sometimes|string',
            'image_files' => 'sometimes|array',
            'image_files.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_url' => 'nullable|string|url',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi|max:10240',
            'preference' => 'nullable|array',
            'measurements' => 'nullable|array',
        ]);

        $product->update($data);

        // Обновление изображений
        if ($request->hasFile('image_files')) {
            $uploadedImages = [];
            foreach ($request->file('image_files') as $image) {
                $path = $image->store('products', 'public');
                $uploadedImages[] = Storage::url($path);
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }
        }

        // Обновление видео
        if ($request->hasFile('video_file')) {
            $videoPath = $request->file('video_file')->store('products/videos', 'public');
            $product->update(['video_url' => Storage::url($videoPath)]);
        }

        return response()->json($product->load('images'), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Получить информацию о продукте",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID продукта", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Информация о продукте", @OA\JsonContent(ref="#/components/schemas/Product")),
     *     @OA\Response(response=404, description="Продукт не найден")
     * )
     */

    public function show(int $id): JsonResponse
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }
        return response()->json($product, 200);
    }
    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Удалить продукт",
     *     security={{"bearerAuth": {}}},
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID продукта", @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Продукт удален"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Продукт не найден")
     * )
     */

    public function destroy(int $id): JsonResponse
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

        // Удаляем изображения
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/products/size/{size_slug}",
     *     summary="Получить продукты по размеру",
     *     tags={"Products"},
     *     @OA\Parameter(name="size_slug", in="path", required=true, description="Slug размера", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Список продуктов", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))),
     *     @OA\Response(response=404, description="Продукт не найден")
     * )
     */

    public function getBySize(string $size_slug): JsonResponse
    {
        $products = Product::whereHas('sizes', function ($query) use ($size_slug) {
            $query->where('slug', $size_slug);
        })->with('images')->get();

        return response()->json($products, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/products/color/{color_id}",
     *     summary="Получить продукты по цвету",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="color_id",
     *         in="path",
     *         required=true,
     *         description="Id цвета",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список продуктов",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *     ),
     *     @OA\Response(response=404, description="Продукт не найден")
     * )
     */

    public function getByColor(string $color_id): JsonResponse
    {
        $products = Product::whereHas('colors', function ($query) use ($color_id) {
            $query->where('id', $color_id);
        })->with('images')->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'Продукты с данным цветом не найдены'], 404);
        }

        return response()->json($products, 200);
    }
}
