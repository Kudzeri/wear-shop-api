<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="Операции с товарами"
 * )
 */
/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Модель продукта",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Футболка"),
 *     @OA\Property(property="category_id", type="integer", example=2),
 *     @OA\Property(property="image_urls", type="array", @OA\Items(type="string", example="https://example.com/image1.jpg")),
 *     @OA\Property(property="video_url", type="string", example="https://example.com/video.mp4"),
 *     @OA\Property(property="description", type="string", example="Классическая футболка для повседневной носки"),
 *     @OA\Property(property="composition_care", type="string", example="100% хлопок, машинная стирка при 30°"),
 *     @OA\Property(property="preference", type="object"),
 *     @OA\Property(property="measurements", type="object"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Получить список всех товаров",
     *     tags={"Products"},
     *     @OA\Response(response=200, description="Список товаров")
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Product::all(), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Создать новый товар",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "category_id", "description"},
     *                 @OA\Property(property="name", type="string", example="Футболка"),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="description", type="string", example="Описание товара"),
     *                 @OA\Property(property="image_urls", type="array", @OA\Items(type="string"), example={"url1.jpg", "url2.jpg"}),
     *                 @OA\Property(property="video_url", type="string", example="https://example.com/video.mp4"),
     *                 @OA\Property(property="image_files", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="video_file", type="string", format="binary"),
     *                 @OA\Property(property="preference", type="object", example={"M": {"size": "M", "params": {"длина": 70, "обхват груди": 100}}}),
     *                 @OA\Property(property="measurements", type="object", example={"M": {"size": "M", "params": {"длина": 70, "обхват груди": 100}}})
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Товар создан"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'category_id' => 'required|integer|exists:categories,id',
                'description' => 'required|string',
                'image_urls' => 'array',
                'image_urls.*' => 'string|url',
                'image_files' => 'array',
                'image_files.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'video_url' => 'nullable|string|url',
                'video_file' => 'nullable|file|mimes:mp4,mov,avi|max:10240',
                'preference' => 'nullable|array',
                'measurements' => 'nullable|array',
            ]);

            // Загрузка изображений
            $uploadedImages = [];
            if ($request->hasFile('image_files')) {
                foreach ($request->file('image_files') as $image) {
                    $uploadedImages[] = Storage::url($image->store('products', 'public'));
                }
            }
            $data['image_urls'] = array_merge($data['image_urls'] ?? [], $uploadedImages);

            // Загрузка видео
            if ($request->hasFile('video_file')) {
                $data['video_url'] = Storage::url($request->file('video_file')->store('products/videos', 'public'));
            }

            $product = Product::create($data);
            return response()->json($product, 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Обновить товар",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Товар обновлен"),
     *     @OA\Response(response=404, description="Товар не найден")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|string',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'description' => 'sometimes|string',
            'image_urls' => 'sometimes|array',
            'image_urls.*' => 'string|url',
            'image_files' => 'sometimes|array',
            'image_files.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_url' => 'nullable|string|url',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi|max:10240',
            'preference' => 'nullable|array',
            'measurements' => 'nullable|array',
        ]);

        if ($request->hasFile('image_files')) {
            $uploadedImages = [];
            foreach ($request->file('image_files') as $image) {
                $uploadedImages[] = Storage::url($image->store('products', 'public'));
            }
            $data['image_urls'] = array_merge($data['image_urls'] ?? $product->image_urls, $uploadedImages);
        }

        if ($request->hasFile('video_file')) {
            $data['video_url'] = Storage::url($request->file('video_file')->store('products/videos', 'public'));
        }

        $product->update($data);
        return response()->json($product, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Получить товар по ID",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Детали товара"),
     *     @OA\Response(response=404, description="Товар не найден")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }
        return response()->json($product, 200);
    }


    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Удалить товар",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Товар удален"),
     *     @OA\Response(response=404, description="Товар не найден")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

        $product->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/products/size/{size_slug}",
     *     summary="Получить товары по размеру",
     *     tags={"Products"},
     *     @OA\Parameter(name="size_slug", in="path", required=true, description="Slug размера", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Список товаров"),
     *     @OA\Response(response=404, description="Размер не найден")
     * )
     */
    public function getBySize(string $size_slug): JsonResponse
    {
        $products = Product::whereHas('sizes', function ($query) use ($size_slug) {
            $query->where('slug', $size_slug);
        })->get();

        return response()->json($products, 200);
    }
}
