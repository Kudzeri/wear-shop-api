<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Регистрация пользователя",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешная регистрация",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Вы успешно зарегестрировались"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="access_token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=501,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => 'string|min:3|max:32|required',
            'email' => 'string|email|unique:users|min:3|max:64|required',
            'password' => 'string|confirmed|min:6|max:16|required',
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 403);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Вы успешно зарегестрировались',
                'user' => $user,
                'access_token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()],501);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Авторизация пользователя",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный вход",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Вы вошли в аккаунт"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="access_token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Неверные учетные данные",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Неверная почта или пароль")
     *         )
     *     ),
     *     @OA\Response(
     *         response=501,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'email' => 'string|email|min:3|max:64|required',
            'password' => 'string|min:6|max:16|required'
        ]);

        if ($validated->fails()){
            return response()->json(['errors' => $validated->errors()], 403);
        }

        $credentials = ['email' => $request->email, 'password' => $request->password];

        try {
            if (!auth()->attempt($credentials)){
                return response()->json([
                    'error' => 'Неверная почта или пароль'
                ],403);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Вы вошли в аккаунт',
                'user' => $user,
                'access_token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 501);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Выход пользователя",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Вы успешно вышли",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Вы вышли из аккаунта")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователь не найден или не авторизован")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден или не авторизован'], 401);
        }

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Вы вышли из аккаунта'
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Получение профиля пользователя",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Информация о пользователе",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-04T12:34:56Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-04T12:34:56Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователь не авторизован")
     *         )
     *     )
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не авторизован'], 401);
        }

        return response()->json($user);
    }

}
