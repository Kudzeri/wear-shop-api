<?php

namespace App\Http\Controllers;
/**
 * @OA\Tag(
 *     name="Subscriptions",
 *     description="Операции с подписками"
 * )
 */


use Illuminate\Http\Request;
/**
 * @OA\Post(
 *     path="/api/subscribe",
 *     summary="Подписка на рассылку",
 *     description="Создает нового подписчика и отправляет промо-код на email.",
 *     tags={"Subscriptions"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "name"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="name", type="string", example="Иван Иванов")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Успешная подписка",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Вы успешно подписались на рассылку!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Ошибка валидации",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The email field is required."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
class SubscriptionController extends Controller
{
    public function subscribe(SubscribeRequest $request)
    {
        $subscriber = Subscriber::firstOrCreate([
            'email' => $request->email,
        ], [
            'name' => $request->name,
        ]);

        Mail::to($subscriber->email)->send(new PromoCodeMail($subscriber));

        return response()->json(['message' => 'Вы успешно подписались на рассылку!']);
    }
}
