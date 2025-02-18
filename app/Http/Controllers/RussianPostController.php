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
     *     path="/russian-post/calculate-delivery",
     *     summary="Расчет стоимости доставки через Почту России",
     *     description="Вычисляет стоимость доставки через Почту России по заданным параметрам: почтовые индексы отправителя и получателя, вес и размеры посылки.",
     *     operationId="russianPostCalculateDelivery",
     *     tags={"RussianPost"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Параметры для расчёта доставки",
     *         @OA\JsonContent(
     *             required={"from_postcode", "to_postcode", "weight", "length", "width", "height"},
     *             @OA\Property(
     *                 property="from_postcode",
     *                 type="string",
     *                 example="101000",
     *                 description="Почтовый индекс отправителя"
     *             ),
     *             @OA\Property(
     *                 property="to_postcode",
     *                 type="string",
     *                 example="190000",
     *                 description="Почтовый индекс получателя"
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
     *     path="/russian-post/create-shipment",
     *     summary="Создание отправления через Почту России",
     *     description="Создает отправление (заказ) через Почту России, используя данные получателя, отправителя и параметры посылки.",
     *     operationId="russianPostCreateShipment",
     *     tags={"RussianPost"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные отправления",
     *         @OA\JsonContent(
     *             required={"recipient", "sender", "package"},
     *             @OA\Property(
     *                 property="recipient",
     *                 type="object",
     *                 description="Данные получателя",
     *                 example={"name": "Иван Иванов", "postcode": "190000", "address": "г. Санкт-Петербург, ул. Пушкина, д.10", "phone": "79991234567"}
     *             ),
     *             @OA\Property(
     *                 property="sender",
     *                 type="object",
     *                 description="Данные отправителя",
     *                 example={"name": "ООО Ромашка", "postcode": "101000", "address": "г. Москва, ул. Ленина, д.1", "phone": "84991234567"}
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
     *     path="/russian-post/track-shipment",
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
}
