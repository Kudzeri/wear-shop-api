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
     *     summary="Расчет стоимости доставки через СДЭК",
     *     operationId="sdekCalculateDelivery",
     *     tags={"SDEK"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"senderCityId", "receiverCityId", "weight", "length", "width", "height"},
     *             @OA\Property(property="senderCityId", type="integer", example=44),
     *             @OA\Property(property="receiverCityId", type="integer", example=137),
     *             @OA\Property(property="weight", type="number", format="float", example=1.5),
     *             @OA\Property(property="length", type="number", example=30),
     *             @OA\Property(property="width", type="number", example=20),
     *             @OA\Property(property="height", type="number", example=10)
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(type="object")),
     *     @OA\Response(response=500, description="Ошибка")
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
     *     summary="Создание отправления через СДЭК",
     *     operationId="sdekCreateShipment",
     *     tags={"SDEK"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"number", "sender", "receiver", "tariff_code", "package"},
     *             @OA\Property(property="number", type="string", example="ORDER-12345"),
     *             @OA\Property(property="tariff_code", type="integer", example=137),
     *             @OA\Property(
     *                 property="sender",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="ООО Ромашка"),
     *                 @OA\Property(property="address", type="string", example="г. Москва, ул. Ленина, д.1"),
     *                 @OA\Property(property="phone", type="string", example="84991234567")
     *             ),
     *             @OA\Property(
     *                 property="receiver",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="address", type="string", example="г. Санкт-Петербург, ул. Пушкина, д.10"),
     *                 @OA\Property(property="phone", type="string", example="79991234567")
     *             ),
     *             @OA\Property(
     *                 property="package",
     *                 type="object",
     *                 @OA\Property(property="weight", type="number", example=1.5),
     *                 @OA\Property(property="length", type="number", example=30),
     *                 @OA\Property(property="width", type="number", example=20),
     *                 @OA\Property(property="height", type="number", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Создано"),
     *     @OA\Response(response=500, description="Ошибка")
     * )
     */
    public function createShipment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'      => 'required|string',
            'tariff_code' => 'required|integer',
            'sender'      => 'required|array',
            'receiver'    => 'required|array',
            'package'     => 'required|array',
            'sender.name'    => 'required|string',
            'sender.address' => 'required|string',
            'sender.phone'   => 'required|string',
            'receiver.name'    => 'required|string',
            'receiver.address' => 'required|string',
            'receiver.phone'   => 'required|string',
            'package.weight' => 'required|numeric',
            'package.length' => 'required|numeric',
            'package.width'  => 'required|numeric',
            'package.height' => 'required|numeric',
        ]);

        $result = $this->sdekService->createShipment($validated);
        if (!$result) {
            return response()->json(['message' => 'Ошибка создания отправления через СДЭК'], 500);
        }
        return response()->json($result, 201);
    }

    /**
     * @OA\Get(
     *     path="/sdek/track-shipment",
     *     summary="Отслеживание отправления через СДЭК",
     *     operationId="sdekTrackShipment",
     *     tags={"SDEK"},
     *     @OA\Parameter(
     *         name="orderNumber",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Номер заказа"
     *     ),
     *     @OA\Response(response=200, description="Информация получена"),
     *     @OA\Response(response=500, description="Ошибка")
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
