<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="CRUD операции с категориями"
 * )
 */
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
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="slug", type="string", example="electronics"),
     *             @OA\Property(property="title", type="string", example="Электроника"),
     *             @OA\Property(property="category_id", type="integer", nullable=true, example=null)
     *         ))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Category::all());
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Создание новой категории",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"slug", "title"},
     *             @OA\Property(property="slug", type="string", example="smartphones"),
     *             @OA\Property(property="title", type="string", example="Смартфоны"),
     *             @OA\Property(property="parent_slug", type="string", example="electronics", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Категория создана"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:categories,slug|max:255',
            'title' => 'required|string|max:255',
            'parent_slug' => 'nullable|string|exists:categories,slug'
        ]);

        $parent = $validated['parent_slug'] ? Category::where('slug', $validated['parent_slug'])->first() : null;

        $category = Category::create([
            'slug' => $validated['slug'],
            'title' => $validated['title'],
            'category_id' => $parent?->id
        ]);

        return response()->json($category, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{slug}",
     *     summary="Получение информации о категории",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug категории",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Информация о категории"),
     *     @OA\Response(response=404, description="Категория не найдена")
     * )
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        return response()->json($category);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{slug}",
     *     summary="Обновление категории",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug категории",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Обновленная категория"),
     *             @OA\Property(property="parent_slug", type="string", example="electronics", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Категория обновлена"),
     *     @OA\Response(response=404, description="Категория не найдена"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'parent_slug' => 'nullable|string|exists:categories,slug'
        ]);

        $parent = $validated['parent_slug'] ? Category::where('slug', $validated['parent_slug'])->first() : null;

        $category->update([
            'title' => $validated['title'] ?? $category->title,
            'category_id' => $parent?->id
        ]);

        return response()->json($category);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{slug}",
     *     summary="Удаление категории",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug категории",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=204, description="Категория удалена"),
     *     @OA\Response(response=404, description="Категория не найдена")
     * )
     */
    public function destroy(string $slug): JsonResponse
    {
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
     *     @OA\Response(response=200, description="Родительская категория"),
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
     *     @OA\Response(response=200, description="Список подкатегорий"),
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
}
