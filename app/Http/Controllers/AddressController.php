<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/addresses",
     *     summary="Получить все адреса пользователя",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Список адресов"),
     *     @OA\Response(response=401, description="Не авторизован")
     * )
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        return response()->json($user->addresses);
    }

    /**
     * @OA\Get(
     *     path="/api/addresses/primary",
     *     summary="Получить основной адрес пользователя",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Основной адрес"),
     *     @OA\Response(response=404, description="Основной адрес не найден")
     * )
     */
    public function primary(): JsonResponse
    {
        $user = Auth::user();
        $primaryAddress = $user->primaryAddress();

        if (!$primaryAddress) {
            return response()->json(['error' => 'Основной адрес не найден'], 404);
        }

        return response()->json($primaryAddress);
    }

    /**
     * @OA\Post(
     *     path="/api/addresses",
     *     summary="Создать новый адрес",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"state", "city", "postal_code", "apartment", "street", "house"},
     *             @OA\Property(property="is_primary", type="boolean"),
     *             @OA\Property(property="state", type="string"),
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="street", type="string"),
     *             @OA\Property(property="house", type="string"),
     *             @OA\Property(property="postal_code", type="string"),
     *             @OA\Property(property="apartment", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Адрес создан"),
     *     @OA\Response(response=400, description="Ошибка валидации")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'is_primary' => 'boolean',
            'state' => 'required|string',
            'city' => 'required|string',
            'street' => 'required|string',
            'house' => 'required|string',
            'postal_code' => 'required|string',
            'apartment' => 'required|string'
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 400);
        }

        $user = Auth::user();

        // Если новый адрес - основной, убираем флаг у других адресов
        if ($request->is_primary) {
            $user->addresses()->update(['is_primary' => false]);
        }

        $address = Address::create($validated->validated());
        $user->addresses()->attach($address->id);

        return response()->json($address, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/addresses/{id}",
     *     summary="Обновить адрес",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="is_primary", type="boolean"),
     *             @OA\Property(property="state", type="string"),
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="street", type="string"),
     *              @OA\Property(property="house", type="string"),
     *             @OA\Property(property="postal_code", type="string"),
     *             @OA\Property(property="apartment", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Адрес обновлен"),
     *     @OA\Response(response=403, description="Нет доступа")
     * )
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        if (!Auth::user()->addresses()->find($address->id)) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $validated = $request->validate([
            'is_primary' => 'boolean',
            'state' => 'string',
            'city' => 'string',
            'street' => 'required|string',
            'house' => 'required|string',
            'postal_code' => 'string',
            'apartment' => 'string'
        ]);

        if ($request->is_primary) {
            Auth::user()->addresses()->update(['is_primary' => false]);
        }

        $address->update($validated);
        return response()->json($address);
    }

    /**
     * @OA\Delete(
     *     path="/api/addresses/{id}",
     *     summary="Удалить адрес",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Адрес удален"),
     *     @OA\Response(response=403, description="Нет доступа")
     * )
     */
    public function destroy(Address $address): JsonResponse
    {
        if (!Auth::user()->addresses()->find($address->id)) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $address->delete();
        return response()->json(['message' => 'Адрес удален'], 204);
    }
}
