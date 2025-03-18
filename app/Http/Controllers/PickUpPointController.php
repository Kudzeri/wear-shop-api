<?php

namespace App\Http\Controllers;

use App\Models\PickUpPoint;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="PickUpPoint")
 */
class PickUpPointController extends Controller
{
    /**
     * @OA\Get(
     *   path="api/pick-up-points",
     *   summary="Получить список ПВЗ",
     *   tags={"PickUpPoint"},
     *   @OA\Response(response=200, description="Список пунктов выдачи")
     * )
     */
    public function index()
    {
        // Возвращает список пунктов выдачи
        $points = PickUpPoint::all();
        return view('pick_up_points.index', compact('points'));
    }

    /**
     * @OA\Get(
     *   path="api/pick-up-points/create",
     *   summary="Форма создания ПВЗ",
     *   tags={"PickUpPoint"},
     *   @OA\Response(response=200, description="Форма создания")
     * )
     */
    public function create()
    {
        return view('pick_up_points.create');
    }

    /**
     * @OA\Post(
     *   path="api/pick-up-points",
     *   summary="Создать ПВЗ",
     *   tags={"PickUpPoint"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/PickUpPoint")
     *   ),
     *   @OA\Response(response=302, description="Перенаправление после создания")
     * )
     */
    public function store(Request $request)
    {
        PickUpPoint::create($request->all());
        return redirect()->route('pick_up_points.index');
    }

    /**
     * @OA\Get(
     *   path="api/pick-up-points/{pickUpPoint}/edit",
     *   summary="Редактировать ПВЗ",
     *   tags={"PickUpPoint"},
     *   @OA\Parameter(
     *     name="pickUpPoint",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Форма редактирования")
     * )
     */
    public function edit(PickUpPoint $pickUpPoint)
    {
        return view('pick_up_points.edit', compact('pickUpPoint'));
    }

    /**
     * @OA\Put(
     *   path="api/pick-up-points/{pickUpPoint}",
     *   summary="Обновить ПВЗ",
     *   tags={"PickUpPoint"},
     *   @OA\Parameter(
     *     name="pickUpPoint",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/PickUpPoint")
     *   ),
     *   @OA\Response(response=302, description="Перенаправление после обновления")
     * )
     */
    public function update(Request $request, PickUpPoint $pickUpPoint)
    {
        $pickUpPoint->update($request->all());
        return redirect()->route('pick_up_points.index');
    }

    /**
     * @OA\Delete(
     *   path="api/pick-up-points/{pickUpPoint}",
     *   summary="Удалить ПВЗ",
     *   tags={"PickUpPoint"},
     *   @OA\Parameter(
     *     name="pickUpPoint",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=302, description="Перенаправление после удаления")
     * )
     */
    public function destroy(PickUpPoint $pickUpPoint)
    {
        $pickUpPoint->delete();
        return redirect()->route('pick_up_points.index');
    }
}