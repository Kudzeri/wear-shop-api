<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Получение всех категорий",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="Список категорий",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Category"))
     *     )
     * )
     */

    public function index(): JsonResponse
    {
        $categories = Category::with('children')->get();
        return response()->json($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Создание новой категории",
     *     security={{"bearerAuth": {}}},
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"slug", "title"},
     *             @OA\Property(property="slug", type="string", example="smartphones"),
     *             @OA\Property(property="title", type="string", example="Смартфоны"),
     *             @OA\Property(property="parent_slug", type="string", nullable=true, example="electronics"),
     *             @OA\Property(property="image", type="string", nullable=true, example="https://example.com/category.jpg")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Категория создана"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */

    public function store(Request $request): JsonResponse
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $validated = $request->validate([
            'slug' => 'required|string|unique:categories,slug|max:255',
            'title' => 'required|string|max:255',
            'parent_slug' => 'nullable|string|exists:categories,slug',
            'image' => 'nullable|string|max:255'
        ]);

        $parent = $validated['parent_slug'] ? Category::where('slug', $validated['parent_slug'])->first() : null;

        $category = Category::create([
            'slug' => $validated['slug'],
            'title' => $validated['title'],
            'category_id' => $parent?->id,
            'image' => $validated['image'] ?? null,
        ]);

        return response()->json($category, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{slug}",
     *     summary="Получение информации о категории",
     *     tags={"Categories"},
     *     @OA\Parameter(name="slug", in="path", required=true, description="Slug категории", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Информация о категории", @OA\JsonContent(ref="#/components/schemas/Category")),
     *     @OA\Response(response=404, description="Категория не найдена")
     * )
     */

    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->with(['children', 'products'])->first();

        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        $category->products->transform(function ($product) {
            $product->discounted_price = $product->getDiscountedPrice();
            return $product;
        });


        return response()->json($category);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{slug}",
     *     summary="Обновление категории",
     *     security={{"bearerAuth": {}}},
     *     tags={"Categories"},
     *     @OA\Parameter(name="slug", in="path", required=true, description="Slug категории", @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Обновленная категория"),
     *             @OA\Property(property="parent_slug", type="string", nullable=true, example="electronics"),
     *             @OA\Property(property="image", type="string", nullable=true, example="https://example.com/category.jpg")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Категория обновлена"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Категория не найдена"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */

    public function update(Request $request, string $slug): JsonResponse
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'parent_slug' => 'nullable|string|exists:categories,slug',
            'image' => 'nullable|string|max:255'
        ]);

        $parent = $validated['parent_slug'] ? Category::where('slug', $validated['parent_slug'])->first() : null;

        $category->update([
            'title' => $validated['title'] ?? $category->title,
            'category_id' => $parent?->id,
            'image' => $validated['image'] ?? $category->image,
        ]);

        return response()->json($category);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{slug}",
     *     summary="Удаление категории",
     *     security={{"bearerAuth": {}}},
     *     tags={"Categories"},
     *     @OA\Parameter(name="slug", in="path", required=true, description="Slug категории", @OA\Schema(type="string")),
     *     @OA\Response(response=204, description="Категория удалена"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Категория не найдена")
     * )
     */

    public function destroy(string $slug): JsonResponse
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        $category->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{slug}/parent",
     *     summary="Получение родительской категории",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug категории",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Родительская категория", @OA\JsonContent(ref="#/components/schemas/Category")),
     *     @OA\Response(response=404, description="Категория не найдена")
     * )
     */

    public function getParent(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->with('parent')->first();
        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        return response()->json($category->parent);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{slug}/children",
     *     summary="Получение всех подкатегорий",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug категории",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Список подкатегорий", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Category"))),
     *     @OA\Response(response=404, description="Категория не найдена")
     * )
     */

    public function getChildren(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->with('children')->first();
        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        return response()->json($category->children);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}/products",
     *     summary="Получить все товары категории и её подкатегорий",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID категории",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список товаров",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *     ),
     *     @OA\Response(response=404, description="Категория не найдена")
     * )
     */

    public function getAllProducts($categoryId): JsonResponse
    {
        $category = Category::with('children')->findOrFail($categoryId);

        $categoryIds = $this->getAllCategoryIds($category);
        $products = Product::whereIn('category_id', $categoryIds)->with('images')->get();

        return response()->json($products);
    }

    private function getAllCategoryIds($category): array
    {
        $ids = [$category->id];

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->getAllCategoryIds($child));
        }

        return $ids;
    }

    /**
     * @OA\Get(
     *     path="/api/categories/on-sale",
     *     summary="Получение категорий со скидками",
     *     tags={"Categories"},
     *     @OA\Response(response=200, description="Список категорий в распродаже", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Category"))),
     *     @OA\Response(response=404, description="Нет категорий в распродаж")
     * )
     */
    public function getSaleCategories(): JsonResponse
    {
        $categories = Category::where('is_sale', true)->with(['children', 'products'])->get();

        if ($categories->isEmpty()) {
            return response()->json(['message' => 'Нет категорий в распродаже'], 404);
        }

        // Добавляем `discounted_price` в каждый продукт внутри категорий
        $categories->transform(function ($category) {
            $category->products->transform(function ($product) {
                $product->discounted_price = $product->getDiscountedPrice();
                return $product;
            });
            return $category;
        });

        return response()->json($categories);
    }

}
