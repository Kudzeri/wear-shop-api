<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PromoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/promos",
     *     summary="Список всех промокодов",
     *     tags={"Promo"},
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function index()
    {
        return Promo::all();
    }

    /**
     * @OA\Post(
     *     path="/api/promos",
     *     summary="Создание промокода",
     *     tags={"Promo"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","discount"},
     *             @OA\Property(property="code", type="string", example="NEW10"),
     *             @OA\Property(property="discount", type="integer", example=10),
     *             @OA\Property(property="expires_at", type="string", format="date", example="2025-12-31")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:promos',
            'discount' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date',
        ]);

        return Promo::create($validated);
    }
}
