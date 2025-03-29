<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Exports\ExportContract;
use App\Exports\OrderExport;
use App\Exports\ProductsExport;
use App\Exports\PromoExport;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Report1CController extends Controller
{
    // Общий метод для экспорта с использованием PhpSpreadsheet
    protected function downloadExport($exportObject, string $filename): BinaryFileResponse
    {
        $data = $exportObject->getData();
        $headings = $exportObject->getHeadings();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Записываем заголовки в первую строку
        $col = 'A';
        foreach ($headings as $heading) {
            $sheet->setCellValue($col . '1', $heading);
            $col++;
        }

        // Записываем данные начиная со второй строки
        $rowIndex = 2;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($col . $rowIndex, $cell);
                $col++;
            }
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * @OA\Get(
     *     path="/api/reports/customers",
     *     summary="Экспорт клиентов",
     *     tags={"Reports"},
     *     @OA\Response(
     *         response=200,
     *         description="Файл экспорта клиентов"
     *     )
     * )
     */
    public function exportCustomers(): BinaryFileResponse
    {
        $export = new CustomersExport();
        return $this->downloadExport($export, 'customers.xlsx');
    }

    /**
     * @OA\Get(
     *     path="/api/reports/orders",
     *     summary="Экспорт заказов",
     *     tags={"Reports"},
     *     @OA\Response(
     *         response=200,
     *         description="Файл экспорта заказов"
     *     )
     * )
     */
    public function exportOrders(): BinaryFileResponse
    {
        $export = new OrderExport();
        return $this->downloadExport($export, 'orders.xlsx');
    }

    /**
     * @OA\Get(
     *     path="/api/reports/products",
     *     summary="Экспорт продуктов",
     *     tags={"Reports"},
     *     @OA\Response(
     *         response=200,
     *         description="Файл экспорта продуктов"
     *     )
     * )
     */
    public function exportProducts(): BinaryFileResponse
    {
        $export = new ProductsExport();
        return $this->downloadExport($export, 'products.xlsx');
    }

    /**
     * @OA\Get(
     *     path="/api/reports/promos",
     *     summary="Экспорт промо-кодов",
     *     tags={"Reports"},
     *     @OA\Response(
     *         response=200,
     *         description="Файл экспорта промо-кодов"
     *     )
     * )
     */
    public function exportPromos(): BinaryFileResponse
    {
        $export = new PromoExport();
        return $this->downloadExport($export, 'promos.xlsx');
    }
}
