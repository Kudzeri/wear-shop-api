<?php

namespace App\Http\Controllers;

use App\Services\SdekService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class SdekController extends Controller
{
    protected SdekService $sdekService;

    public function __construct(SdekService $sdekService)
    {
        $this->sdekService = $sdekService;
    }

    /**
     * @OA\Post(
     *     path="/sdek/calculate-delivery",
     *     summary="Расчет стоимости доставки через СДЭК (v2)",
     *     description="Вычисляет стоимость доставки через СДЭК по заданным параметрам: ID города отправителя, ID города получателя, вес и размеры посылки.",
     *     operationId="sdekCalculateDelivery",
     *     tags={"SDEK"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Параметры для расчёта доставки",
     *         @OA\JsonContent(
     *             required={"senderCityId", "receiverCityId", "weight", "length", "width", "height"},
     *             @OA\Property(
     *                 property="senderCityId",
     *                 type="integer",
     *                 example=44,
     *                 description="ID города отправителя"
     *             ),
     *             @OA\Property(
     *                 property="receiverCityId",
     *                 type="integer",
     *                 example=137,
     *                 description="ID города получателя"
     *             ),
     *             @OA\Property(
     *                 property="weight",
     *                 type="number",
     *                 format="float",
     *                 example=1.5,
     *                 description="Вес посылки в кг"
     *             ),
     *             @OA\Property(
     *                 property="length",
     *                 type="number",
     *                 example=30,
     *                 description="Длина посылки в см"
     *             ),
     *             @OA\Property(
     *                 property="width",
     *                 type="number",
     *                 example=20,
     *                 description="Ширина посылки в см"
     *             ),
     *             @OA\Property(
     *                 property="height",
     *                 type="number",
     *                 example=10,
     *                 description="Высота посылки в см"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Стоимость доставки рассчитана",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка расчёта доставки через СДЭК",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка расчёта доставки через СДЭК")
     *         )
     *     )
     * )
     */
    public function calculateDelivery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'senderCityId'   => 'required|integer',
            'receiverCityId' => 'required|integer',
            'weight'         => 'required|numeric',
            'length'         => 'required|numeric',
            'width'          => 'required|numeric',
            'height'         => 'required|numeric',
        ]);

        $result = $this->sdekService->calculateDeliveryCost($validated);
        if (!$result) {
            return response()->json(['message' => 'Ошибка расчёта доставки через СДЭК'], 500);
        }
        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/sdek/create-shipment",
     *     summary="Создание отправления через СДЭК (v2)",
     *     description="Создает отправление (заказ) через СДЭК, используя переданные данные: уникальный номер заказа, данные отправителя и получателя, тарифный код и параметры посылки.",
     *     operationId="sdekCreateShipment",
     *     tags={"SDEK"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные отправления",
     *         @OA\JsonContent(
     *             required={"number", "sender", "receiver", "tariff_code", "package"},
     *             @OA\Property(
     *                 property="number",
     *                 type="string",
     *                 example="ORDER-12345",
     *                 description="Уникальный номер заказа"
     *             ),
     *             @OA\Property(
     *                 property="sender",
     *                 type="object",
     *                 description="Данные отправителя",
     *                 example={"name": "ООО Ромашка", "address": "г. Москва, ул. Ленина, д.1", "phone": "84991234567"}
     *             ),
     *             @OA\Property(
     *                 property="receiver",
     *                 type="object",
     *                 description="Данные получателя",
     *                 example={"name": "Иван Иванов", "address": "г. Санкт-Петербург, ул. Пушкина, д.10", "phone": "79991234567"}
     *             ),
     *             @OA\Property(
     *                 property="tariff_code",
     *                 type="integer",
     *                 example=137,
     *                 description="Код тарифа СДЭК"
     *             ),
     *             @OA\Property(
     *                 property="package",
     *                 type="object",
     *                 description="Параметры посылки",
     *                 example={"weight": 1.5, "length": 30, "width": 20, "height": 10}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Отправление успешно создано",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка создания отправления через СДЭК",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка создания отправления через СДЭК")
     *         )
     *     )
     * )
     */
    public function createShipment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'      => 'required|string',
            'sender'      => 'required|array',
            'receiver'    => 'required|array',
            'tariff_code' => 'required|integer',
            'package'     => 'required|array',
        ]);

        $result = $this->sdekService->createShipment($validated);
        if (!$result) {
            return response()->json(['message' => 'Ошибка создания отправления через СДЭК'], 500);
        }
        return response()->json($result, 201);
    }

    /**
     * @OA\Post(
     *     path="/sdek/track-shipment",
     *     summary="Отслеживание отправления через СДЭК (v2)",
     *     description="Возвращает статус отправления через СДЭК по уникальному номеру заказа.",
     *     operationId="sdekTrackShipment",
     *     tags={"SDEK"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Номер заказа для отслеживания отправления",
     *         @OA\JsonContent(
     *             required={"orderNumber"},
     *             @OA\Property(
     *                 property="orderNumber",
     *                 type="string",
     *                 example="ORDER-12345",
     *                 description="Уникальный номер заказа, используемый для отслеживания"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о статусе отправления получена",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка отслеживания отправления через СДЭК",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка отслеживания отправления через СДЭК")
     *         )
     *     )
     * )
     */
    public function trackShipment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderNumber' => 'required|string',
        ]);

        $result = $this->sdekService->trackShipment($validated['orderNumber']);
        if (!$result) {
            return response()->json(['message' => 'Ошибка отслеживания отправления через СДЭК'], 500);
        }
        return response()->json($result);
    }
}
