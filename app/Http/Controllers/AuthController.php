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

    /**
     * @OA\Post(
     *     path="/api/user/avatar",
     *     summary="Изменение аватара пользователя",
     *     description="Позволяет пользователю загрузить новый аватар.",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"avatar"},
     *                 @OA\Property(property="avatar", type="string", format="binary", description="Файл изображения")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Аватар обновлен", @OA\JsonContent(@OA\Property(property="avatar_url", type="string"))),
     *     @OA\Response(response=400, description="Ошибка загрузки файла"),
     *     @OA\Response(response=401, description="Не авторизован")
     * )
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        if (!$request->hasFile('avatar')) {
            return response()->json(['message' => 'Файл не загружен'], 400)
                ->header('Access-Control-Allow-Origin', '*');
        }


        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar_url' => asset('storage/' . $path)]);
            return response()->json(['avatar_url' => $user->avatar_url], 200);
        }

        return response()->json(['message' => 'Ошибка загрузки файла'], 400);
    }

    /**
     * @OA\Put(
     *     path="/api/user/password",
     *     summary="Обновление пароля",
     *     description="Позволяет пользователю изменить свой пароль.",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldpassword123"),
     *             @OA\Property(property="new_password", type="string", example="newpassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Пароль обновлен"),
     *     @OA\Response(response=400, description="Ошибка валидации"),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=403, description="Текущий пароль не совпадает")
     * )
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|max:16|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Текущий пароль не совпадает'], 403);
        }

        $user->update(['password' => Hash::make($validated['new_password'])]);

        return response()->json(['message' => 'Пароль успешно обновлен'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user/loyalty",
     *     summary="Получение информации о лояльности",
     *     description="Возвращает текущий уровень лояльности пользователя и его скидку.",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Информация о лояльности",
     *         @OA\JsonContent(
     *             @OA\Property(property="level", type="string", example="Gold"),
     *             @OA\Property(property="discount_percentage", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не авторизован")
     * )
     */
    public function getLoyaltyInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $level = $user->loyaltyLevel;
        $discount = $level ? $level->discount_percentage : 0;

        return response()->json([
            'level' => $level ? $level->name : 'Нет уровня',
            'discount_percentage' => $discount
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/profile",
     *     summary="Обновление данных профиля",
     *     description="Позволяет пользователю изменить свои личные данные.",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="surname", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="+77011234567"),
     *             @OA\Property(property="avatar", type="string", format="binary", description="Файл изображения (jpeg, png, jpg, gif)")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Профиль обновлен", @OA\JsonContent(@OA\Property(property="message", type="string", example="Профиль обновлен"))),
     *     @OA\Response(response=400, description="Ошибка валидации"),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|min:3|max:32',
            'surname' => 'sometimes|string|min:3|max:32',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|min:10|max:20',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Обновление аватара
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_url'] = asset('storage/' . $path);
        }

        // Обновляем данные пользователя
        $user->update($validated);

        return response()->json(['message' => 'Профиль обновлен', 'user' => $user], 200);
    }

}
