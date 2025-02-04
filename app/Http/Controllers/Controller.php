<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Server API",
 *     version="1.0.0",
 *     description="API сайта"
 * )
 *
 * @OA\Server(url="http://127.0.0.1:8000")
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Введите токен в формате Bearer {your_token}"
 * )
 */
abstract class Controller
{
    //
}
