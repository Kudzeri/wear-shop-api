<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(name="User", description="Работа с пользователями")
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Получить всех пользователей",
     *     description="Возвращает список всех пользователей",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="Список пользователей",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван"),
     *                 @OA\Property(property="surname", type="string", example="Иванов"),
     *                 @OA\Property(property="email", type="string", example="ivan@example.com"),
     *                 @OA\Property(property="phone", type="string", example="+7 123 456 78 90"),
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Получить пользователя по ID",
     *     description="Возвращает пользователя по ID",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пользователь найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Иван"),
     *             @OA\Property(property="surname", type="string", example="Иванов"),
     *             @OA\Property(property="email", type="string", example="ivan@example.com"),
     *             @OA\Property(property="phone", type="string", example="+7 123 456 78 90"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $user = User::find($id);
        if ($user) {
            return response()->json($user, 200);
        }
        return response()->json(['message' => 'Пользователь не найден'], 404);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Создать нового пользователя",
     *     description="Создает нового пользователя",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Иван"),
     *             @OA\Property(property="surname", type="string", example="Иванов"),
     *             @OA\Property(property="email", type="string", example="ivan@example.com"),
     *             @OA\Property(property="phone", type="string", example="+7 123 456 78 90"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Пользователь успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Иван"),
     *             @OA\Property(property="surname", type="string", example="Иванов"),
     *             @OA\Property(property="email", type="string", example="ivan@example.com"),
     *             @OA\Property(property="phone", type="string", example="+7 123 456 78 90"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Некорректные данные"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Обновить информацию о пользователе",
     *     description="Обновляет информацию о пользователе",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Иван"),
     *             @OA\Property(property="surname", type="string", example="Иванов"),
     *             @OA\Property(property="email", type="string", example="ivan@example.com"),
     *             @OA\Property(property="phone", type="string", example="+7 123 456 78 90")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пользователь успешно обновлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Иван"),
     *             @OA\Property(property="surname", type="string", example="Иванов"),
     *             @OA\Property(property="email", type="string", example="ivan@example.com"),
     *             @OA\Property(property="phone", type="string", example="+7 123 456 78 90"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Некорректные данные"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'surname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user->update($request->only(['name', 'surname', 'email', 'phone']));

        return response()->json($user, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Удалить пользователя",
     *     description="Удаляет пользователя по ID",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пользователь успешно удален"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Пользователь успешно удален'], 200);
    }
}
