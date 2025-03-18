<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use App\Exports\ProductsExport;
use App\Exports\PromoExport;
use App\Exports\OrderExport;

/**
 * @OA\Info(
 *     title="1C Export API",
 *     version="1.0.0",
 *     description="API для формирования отчетов для 1С"
 * )
 */

/**
 * @OA\Tag(
 *     name="Reports",
 *     description="Endpoints для экспорта отчетов"
 * )
 */
class Report1CController extends Controller
{
    /**
     * @OA\Get(
     *     path="/report/customers",
     *     summary="Экспорт отчета покупателей",
     *     tags={"Reports"},
     *     @OA\Parameter(
     *         name="id_customer",
     *         in="query",
     *         description="Фильтр по ID покупателя",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Отчет покупателей в формате xlsx"
     *     )
     * )
     */
    public function exportCustomers(Request $request)
    {
        $filters = $request->all();
        return Excel::download(new CustomersExport($filters), 'customers.xlsx');
    }

    /**
     * @OA\Get(
     *     path="/report/products",
     *     summary="Экспорт отчета товаров",
     *     tags={"Reports"},
     *     @OA\Parameter(
     *         name="id_product",
     *         in="query",
     *         description="Фильтр по ID товара",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Отчет товаров в формате xlsx"
     *     )
     * )
     */
    public function exportProducts(Request $request)
    {
        $filters = $request->all();
        return Excel::download(new ProductsExport($filters), 'products.xlsx');
    }

    /**
     * @OA\Get(
     *     path="/report/promos",
     *     summary="Экспорт отчета промокодов",
     *     tags={"Reports"},
     *     @OA\Parameter(
     *         name="id_promo",
     *         in="query",
     *         description="Фильтр по ID промокода",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Отчет промокодов в формате xlsx"
     *     )
     * )
     */
    public function exportPromos(Request $request)
    {
        $filters = $request->all();
        return Excel::download(new PromoExport($filters), 'promos.xlsx');
    }

    /**
     * @OA\Get(
     *     path="/report/orders",
     *     summary="Экспорт отчета заказов покупателей",
     *     tags={"Reports"},
     *     @OA\Parameter(
     *         name="id_order",
     *         in="query",
     *         description="Фильтр по ID заказа",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Отчет заказов в формате xlsx"
     *     )
     * )
     */
    public function exportOrders(Request $request)
    {
        $filters = $request->all();
        return Excel::download(new OrderExport($filters), 'orders.xlsx');
    }
}
