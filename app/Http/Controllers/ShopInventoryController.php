<?php

namespace App\Http\Controllers;

use App\Action\Inventory\CreateInventory;
use App\Action\Inventory\DeleteInventory;
use App\Action\Inventory\GetAllInventories;
use App\Action\Inventory\GetAllInventoriesRaw;
use App\Action\Inventory\UpdateInventory;
use App\Action\Inventory\ExportInventoryExcel;
use App\Action\Inventory\ExportInventoryPdf;
use App\Action\Inventory\GenerateInventoryReport;
use App\Action\Inventory\GetCategoriesAndBrands;
use App\Http\Requests\Inventory\GetAllInventoriesRequest;
use App\Http\Requests\Inventory\ShopInventoryRequest;
use App\Http\Requests\Inventory\InventoryReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ShopInventoryController extends Controller
{
    public function getAllShopInventories(GetAllInventoriesRequest $request, GetAllInventories $getAllInventories): JsonResponse
    {
        $result = $getAllInventories($request->validated());
        return response()->json($result);
    }

    public function createShopInventory(ShopInventoryRequest $request, CreateInventory $createInventory): JsonResponse
    {
        return response()->json($createInventory($request->validated()));
    }

    public function updateShopInventories(string $id, ShopInventoryRequest $request, UpdateInventory $updateInventory): JsonResponse
    {
        return response()->json($updateInventory($id, $request->validated()));
    }

    public function deleteShopInventories(string $id, DeleteInventory $deleteInventory): JsonResponse
    {
        return response()->json($deleteInventory($id));
    }

    public function getAllShopInventoriesRaw(GetAllInventoriesRaw $getAllInventoriesRaw): JsonResponse
    {
        $result = $getAllInventoriesRaw();
        return response()->json($result);
    }

    public function exportExcel(GetAllInventoriesRequest $request, ExportInventoryExcel $action): BinaryFileResponse
    {
        return $action($request->validated());
    }

    public function exportPdf(GetAllInventoriesRequest $request, ExportInventoryPdf $action): Response
    {
        return $action($request->validated());
    }

    public function generateReport(InventoryReportRequest $request, GenerateInventoryReport $action): JsonResponse
    {
        return response()->json($action($request->validated()));
    }

    public function getCategoriesAndBrands(GetCategoriesAndBrands $action): JsonResponse
    {
        return response()->json($action());
    }
}
