<?php

namespace App\Http\Controllers;

use App\Action\Inventory\CreateInventory;
use App\Action\Inventory\DeleteInventory;
use App\Action\Inventory\GetAllInventories;
use App\Action\Inventory\UpdateInventory;
use App\Http\Requests\Inventory\ShopInventoryRequest;
use Illuminate\Http\JsonResponse;

class ShopInventoryController extends Controller
{
    public function getAllShopInventories(GetAllInventories $getAllInventories): JsonResponse
    {
        return response()->json($getAllInventories());
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
}

