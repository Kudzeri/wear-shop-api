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
     *     path="/api/sdek/calculate-delivery",
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
     *             @OA\Property(property="length", type="integer", example=30),
     *             @OA\Property(property="width", type="integer", example=20),
     *             @OA\Property(property="height", type="integer", example=10),
     *             @OA\Property(property="senderPostalCode", type="string", example="123456"),
     *             @OA\Property(property="receiverPostalCode", type="string", example="654321"),
     *             @OA\Property(property="senderCountryCode", type="string", example="RU"),
     *             @OA\Property(property="receiverCountryCode", type="string", example="RU"),
     *             @OA\Property(property="senderCity", type="string", example="Москва"),
     *             @OA\Property(property="receiverCity", type="string", example="Санкт-Петербург"),
     *             @OA\Property(property="senderAddress", type="string", example="ул. Ленина, д. 1"),
     *             @OA\Property(property="receiverAddress", type="string", example="пр. Мира, д. 2"),
     *             @OA\Property(property="senderContragentType", type="string", example="sender"),
     *             @OA\Property(property="receiverContragentType", type="string", example="recipient")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="tariff_codes",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="tariff_code", type="integer", example=136),
     *                     @OA\Property(property="tariff_name", type="string", example="Экспресс лайт склад-дверь"),
     *                     @OA\Property(property="tariff_description", type="string", example="Доставка с минимальной стоимостью"),
     *                     @OA\Property(property="delivery_mode", type="integer", example=1),
     *                     @OA\Property(property="delivery_sum", type="number", format="float", example=340.5),
     *                     @OA\Property(property="period_min", type="integer", example=2),
     *                     @OA\Property(property="period_max", type="integer", example=4),
     *                     @OA\Property(property="calendar_min", type="integer", example=3),
     *                     @OA\Property(property="calendar_max", type="integer", example=5),
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="code", type="string", example="ERR001"),
     *                     @OA\Property(property="message", type="string", example="Ошибка в адресе получателя")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="warnings",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="code", type="string", example="WRN001"),
     *                     @OA\Property(property="message", type="string", example="Расчет может быть не точен")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Ошибка")
     * )
     */
    public function calculateDelivery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'senderCityId'           => 'required|integer',
            'receiverCityId'         => 'required|integer',
            'weight'                 => 'required|numeric',
            'length'                 => 'required|numeric',
            'width'                  => 'required|numeric',
            'height'                 => 'required|numeric',
            'senderPostalCode'       => 'nullable|string',
            'receiverPostalCode'     => 'nullable|string',
            'senderCountryCode'      => 'nullable|string|max:2',
            'receiverCountryCode'    => 'nullable|string|max:2',
            'senderCity'             => 'nullable|string',
            'receiverCity'           => 'nullable|string',
            'senderAddress'          => 'nullable|string',
            'receiverAddress'        => 'nullable|string',
            'senderContragentType'   => 'nullable|string',
            'receiverContragentType' => 'nullable|string',
        ]);

        $result = $this->sdekService->calculateDeliveryCost($validated);
        if (!$result) {
            return response()->json(['message' => 'Ошибка расчёта доставки через СДЭК'], 500);
        }
        return response()->json($result);
    }


    /**
     * @OA\Post(
     *     path="/api/sdek/create-shipment",
     *     summary="Создание отправления через СДЭК",
     *     operationId="sdekCreateShipment",
     *     tags={"SDEK"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"number", "tariff_code", "sender", "recipient", "from_location", "to_location", "packages"},
     *             @OA\Property(property="uuid", type="string", format="uuid", example="095be615-a8ad-4c33-8e9c-c7612fbf6c9f"),
     *             @OA\Property(property="number", type="string", example="ORDER-12345"),
     *             @OA\Property(property="tariff_code", type="integer", example=137),
     *             @OA\Property(property="comment", type="string", example="Обычная доставка"),

     *             @OA\Property(
     *                 property="sender",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="ООО Ромашка"),
     *                 @OA\Property(property="company", type="string", example="ООО Ромашка"),
     *                 @OA\Property(property="contragent_type", type="string", example="LEGAL_ENTITY"),
     *                 @OA\Property(property="phones", type="array", @OA\Items(
     *                     @OA\Property(property="number", type="string", example="84991234567")
     *                 ))
     *             ),
     *             @OA\Property(
     *                 property="recipient",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="company", type="string", example="ИП Иванов"),
     *                 @OA\Property(property="contragent_type", type="string", example="INDIVIDUAL"),
     *                 @OA\Property(property="phones", type="array", @OA\Items(
     *                     @OA\Property(property="number", type="string", example="79991234567")
     *                 ))
     *             ),
     *             @OA\Property(
     *                 property="from_location",
     *                 type="object",
     *                 @OA\Property(property="code", type="integer", example=44),
     *                 @OA\Property(property="address", type="string", example="г. Москва, ул. Ленина, д.1")
     *             ),
     *             @OA\Property(
     *                 property="to_location",
     *                 type="object",
     *                 @OA\Property(property="code", type="integer", example=137),
     *                 @OA\Property(property="address", type="string", example="г. Санкт-Петербург, ул. Пушкина, д.10")
     *             ),
     *             @OA\Property(
     *                 property="packages",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="number", type="string", example="1"),
     *                     @OA\Property(property="weight", type="integer", example=1500),
     *                     @OA\Property(property="length", type="integer", example=30),
     *                     @OA\Property(property="width", type="integer", example=20),
     *                     @OA\Property(property="height", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Создано",
     *         @OA\JsonContent(
     *             @OA\Property(property="entity", type="object",
     *                 @OA\Property(property="uuid", type="string", format="uuid", example="095be615-a8ad-4c33-8e9c-c7612fbf6c9f")
     *             ),
     *             @OA\Property(property="requests", type="array", @OA\Items(
     *                 @OA\Property(property="request_uuid", type="string", example="a699086b-c336-457e-9191-0c825d6efbc8"),
     *                 @OA\Property(property="state", type="string", example="ACCEPTED")
     *             )),
     *             @OA\Property(property="related_entities", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", example="095be615-a8ad-4c33-8e9c-c7612fbf6c9f"),
     *                 @OA\Property(property="type", type="string", example="return_order"),
     *                 @OA\Property(property="cdek_number", type="string", example="123456789"),
     *                 @OA\Property(property="create_time", type="string", format="date-time", example="2024-03-27T14:15:22Z")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Ошибка")
     * )
     */
    public function createShipment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuid'            => 'nullable|uuid',
            'number'          => 'required|string',
            'tariff_code'     => 'required|integer',
            'comment'         => 'nullable|string',

            'sender'                      => 'required|array',
            'sender.name'                => 'required|string',
            'sender.company'             => 'nullable|string',
            'sender.contragent_type'     => 'required|string',
            'sender.phones'              => 'required|array|min:1',
            'sender.phones.*.number'     => 'required|string',

            'recipient'                      => 'required|array',
            'recipient.name'                => 'required|string',
            'recipient.company'             => 'nullable|string',
            'recipient.contragent_type'     => 'required|string',
            'recipient.phones'              => 'required|array|min:1',
            'recipient.phones.*.number'     => 'required|string',

            'from_location'             => 'required|array',
            'from_location.code'        => 'required|integer',
            'from_location.address'     => 'required|string',

            'to_location'               => 'required|array',
            'to_location.code'          => 'required|integer',
            'to_location.address'       => 'required|string',

            'packages'                  => 'required|array|min:1',
            'packages.*.number'         => 'nullable|string',
            'packages.*.weight'         => 'required|integer',
            'packages.*.length'         => 'required|integer',
            'packages.*.width'          => 'required|integer',
            'packages.*.height'         => 'required|integer',
        ]);

        $result = $this->sdekService->createShipment($validated);
        if (!$result) {
            return response()->json(['message' => 'Ошибка создания отправления через СДЭК'], 500);
        }
        return response()->json($result, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/sdek/track-shipment",
     *     summary="Отслеживание отправления через СДЭК",
     *     description="Возвращает статус отправления по номеру заказа, указанному при создании.",
     *     operationId="sdekTrackShipment",
     *     tags={"SDEK"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orderNumber",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Номер заказа, указанный при создании отправления"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация об отправлении успешно получена",
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

    /**
     * @OA\Get(
     *     path="/api/sdek/shipment-by-uuid",
     *     summary="Получение информации о заказе по UUID через СДЭК",
     *     operationId="sdekGetShipmentByUuid",
     *     tags={"SDEK"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="query",
     *         required=true,
     *         description="UUID заказа",
     *         @OA\Schema(type="string", format="uuid", example="095be615-a8ad-4c33-8e9c-c7612fbf6c9f")
     *     ),
     *     @OA\Response(response=200, description="Информация получена"),
     *     @OA\Response(response=500, description="Ошибка")
     * )
     */
    public function getShipmentByUuid(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuid' => 'required|uuid',
        ]);

        $result = $this->sdekService->getShipmentByUuid($validated['uuid']);
        if (!$result) {
            return response()->json(['message' => 'Ошибка получения данных по UUID через СДЭК'], 500);
        }

        return response()->json($result);
    }

}
