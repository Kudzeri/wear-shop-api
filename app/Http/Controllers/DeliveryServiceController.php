<?php

namespace App\Http\Controllers;

use App\Models\DeliveryService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="DeliveryService")
 */
class DeliveryServiceController extends Controller
{
    /**
     * @OA\Get(
     *   path="api/delivery-services",
     *   summary="Получить список служб доставки",
     *   tags={"DeliveryService"},
     *   @OA\Response(response=200, description="Список служб доставки")
     * )
     */
    public function index()
    {
        // Возвращает список служб доставки
        $services = DeliveryService::all();
        return view('delivery_services.index', compact('services'));
    }

    /**
     * @OA\Get(
     *   path="api/delivery-services/create",
     *   summary="Форма создания службы доставки",
     *   tags={"DeliveryService"},
     *   @OA\Response(response=200, description="Форма создания")
     * )
     */
    public function create()
    {
        return view('delivery_services.create');
    }

    /**
     * @OA\Post(
     *   path="api/delivery-services",
     *   summary="Создать службу доставки",
     *   tags={"DeliveryService"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/DeliveryService")
     *   ),
     *   @OA\Response(response=302, description="Перенаправление после создания")
     * )
     */
    public function store(Request $request)
    {
        DeliveryService::create($request->all());
        return redirect()->route('delivery_services.index');
    }

    /**
     * @OA\Get(
     *   path="api/delivery-services/{deliveryService}/edit",
     *   summary="Редактировать службу доставки",
     *   tags={"DeliveryService"},
     *   @OA\Parameter(
     *     name="deliveryService",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Форма редактирования")
     * )
     */
    public function edit(DeliveryService $deliveryService)
    {
        return view('delivery_services.edit', compact('deliveryService'));
    }

    /**
     * @OA\Put(
     *   path="api/delivery-services/{deliveryService}",
     *   summary="Обновить службу доставки",
     *   tags={"DeliveryService"},
     *   @OA\Parameter(
     *     name="deliveryService",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/DeliveryService")
     *   ),
     *   @OA\Response(response=302, description="Перенаправление после обновления")
     * )
     */
    public function update(Request $request, DeliveryService $deliveryService)
    {
        $deliveryService->update($request->all());
        return redirect()->route('delivery_services.index');
    }

    /**
     * @OA\Delete(
     *   path="api/delivery-services/{deliveryService}",
     *   summary="Удалить службу доставки",
     *   tags={"DeliveryService"},
     *   @OA\Parameter(
     *     name="deliveryService",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=302, description="Перенаправление после удаления")
     * )
     */
    public function destroy(DeliveryService $deliveryService)
    {
        $deliveryService->delete();
        return redirect()->route('delivery_services.index');
    }
}