<?php

namespace App\Http\Controllers;

use App\Services\RussianPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class RussianPostController extends Controller
{
    protected RussianPostService $russianPostService;

    public function __construct(RussianPostService $russianPostService)
    {
        $this->russianPostService = $russianPostService;
    }

    /**
     * @OA\Post(
     *     path="/api/russian-post/calculate-delivery",
     *     summary="Расчет стоимости доставки через Почту России",
     *     description="Вычисляет стоимость доставки по заданным параметрам: почтовые индексы, вес и габариты посылки. Значения возвращаются в копейках.",
     *     operationId="russianPostCalculateDelivery",
     *     tags={"RussianPost"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Параметры для расчета стоимости пересылки",
     *         @OA\JsonContent(
     *             required={"from_postcode", "to_postcode", "weight", "length", "width", "height"},
     *             @OA\Property(property="from_postcode", type="string", example="101000", description="Почтовый индекс отправителя"),
     *             @OA\Property(property="to_postcode", type="string", example="190000", description="Почтовый индекс получателя"),
     *             @OA\Property(property="weight", type="number", format="float", example=1.2, description="Вес в кг"),
     *             @OA\Property(property="length", type="integer", example=30, description="Длина в см"),
     *             @OA\Property(property="width", type="integer", example=20, description="Ширина в см"),
     *             @OA\Property(property="height", type="integer", example=10, description="Высота в см"),
     *             @OA\Property(property="fragile", type="boolean", example=true, description="Хрупкая посылка (опционально)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Стоимость доставки рассчитана",
     *         @OA\JsonContent(
     *             @OA\Property(property="total-rate", type="integer", example=32000, description="Общая стоимость пересылки в копейках"),
     *             @OA\Property(property="ground-rate", type="object",
     *                 @OA\Property(property="rate", type="integer", example=30000),
     *                 @OA\Property(property="vat", type="integer", example=5000)
     *             ),
     *             @OA\Property(property="delivery-time", type="object",
     *                 @OA\Property(property="min-days", type="integer", example=2),
     *                 @OA\Property(property="max-days", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка расчёта доставки через Почту России",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка расчёта доставки через Почту России")
     *         )
     *     )
     * )
     */
    public function calculateDelivery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_postcode' => 'required|string',
            'to_postcode'   => 'required|string',
            'weight'        => 'required|numeric',
            'length'        => 'required|numeric',
            'width'         => 'required|numeric',
            'height'        => 'required|numeric',
        ]);

        $result = $this->russianPostService->calculateDeliveryCost($validated);
        if (!$result) {
            return response()->json(['message' => 'Ошибка расчёта доставки через Почту России'], 500);
        }
        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/russian-post/create-shipment",
     *     summary="Создание отправления через Почту России",
     *     description="Создает отправление (заказ) через Почту России с параметрами получателя и посылки. Использует API PUT /1.0/user/backlog.",
     *     operationId="russianPostCreateShipment",
     *     tags={"RussianPost"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные отправления",
     *         @OA\JsonContent(
     *             required={"order_num", "recipient", "package"},
     *             @OA\Property(property="order_num", type="string", example="ORDER-12345", description="Уникальный номер заказа"),
     *             @OA\Property(
     *                 property="recipient",
     *                 type="object",
     *                 required={"name", "postcode", "region", "city", "street", "house", "phone"},
     *                 description="Данные получателя",
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="postcode", type="string", example="190000"),
     *                 @OA\Property(property="region", type="string", example="Ленинградская обл."),
     *                 @OA\Property(property="city", type="string", example="Санкт-Петербург"),
     *                 @OA\Property(property="street", type="string", example="ул. Пушкина"),
     *                 @OA\Property(property="house", type="string", example="10"),
     *                 @OA\Property(property="room", type="string", example="5"),
     *                 @OA\Property(property="phone", type="string", example="79991234567")
     *             ),
     *             @OA\Property(
     *                 property="package",
     *                 type="object",
     *                 required={"weight", "length", "width", "height"},
     *                 description="Параметры посылки",
     *                 @OA\Property(property="weight", type="number", example=1.5, description="Вес в кг"),
     *                 @OA\Property(property="length", type="integer", example=30, description="Длина в см"),
     *                 @OA\Property(property="width", type="integer", example=20, description="Ширина в см"),
     *                 @OA\Property(property="height", type="integer", example=10, description="Высота в см"),
     *                 @OA\Property(property="value", type="integer", example=5000, description="Объявленная ценность в копейках"),
     *                 @OA\Property(property="fragile", type="boolean", example=true, description="Хрупкий груз")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Отправление успешно создано",
     *         @OA\JsonContent(
     *             @OA\Property(property="result-ids", type="array", @OA\Items(type="integer", example=123456)),
     *             @OA\Property(property="errors", type="array", @OA\Items(
     *                 @OA\Property(property="position", type="integer", example=0),
     *                 @OA\Property(property="error-codes", type="array", @OA\Items(
     *                     @OA\Property(property="code", type="string", example="UNDEFINED"),
     *                     @OA\Property(property="description", type="string", example="Ошибка обработки запроса"),
     *                     @OA\Property(property="details", type="string", example="index-to is invalid")
     *                 ))
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка создания отправления через Почту России",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка создания отправления через Почту России")
     *         )
     *     )
     * )
     */
    public function createShipment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient' => 'required|array',
            'sender'    => 'required|array',
            'package'   => 'required|array',
        ]);

        $result = $this->russianPostService->createShipment($validated);
        if (!$result) {
            return response()->json(['message' => 'Ошибка создания отправления через Почту России'], 500);
        }
        return response()->json($result, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/russian-post/track-shipment",
     *     summary="Отслеживание отправления через Почту России",
     *     description="Возвращает информацию о статусе отправления по номеру отслеживания.",
     *     operationId="russianPostTrackShipment",
     *     tags={"RussianPost"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Номер отслеживания отправления",
     *         @OA\JsonContent(
     *             required={"trackingNumber"},
     *             @OA\Property(
     *                 property="trackingNumber",
     *                 type="string",
     *                 example="PO123456789RU",
     *                 description="Номер отслеживания отправления"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация об отправлении получена",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка отслеживания отправления через Почту России",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка отслеживания отправления через Почту России")
     *         )
     *     )
     * )
     */
    public function trackShipment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trackingNumber' => 'required|string',
        ]);

        $result = $this->russianPostService->trackShipment($validated['trackingNumber']);
        if (!$result) {
            return response()->json(['message' => 'Ошибка отслеживания отправления через Почту России'], 500);
        }
        return response()->json($result);
    }

    /**
     * @OA\Get(
     *     path="/api/russian-post/search-order",
     *     summary="Поиск отправления по номеру заказа",
     *     description="Возвращает информацию об отправлении по order-num (идентификатору заказа магазина).",
     *     operationId="russianPostSearchOrder",
     *     tags={"RussianPost"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orderNumber",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Номер заказа (order-num)"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация об отправлении найдена",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Заказ не найден")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка поиска заказа",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка поиска отправления через Почту России")
     *         )
     *     )
     * )
     */
    public function searchOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderNumber' => 'required|string',
        ]);

        $result = $this->russianPostService->findShipmentByOrderNumber($validated['orderNumber']);

        if (empty($result)) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        return response()->json($result);
    }

}
