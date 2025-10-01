<?php

namespace App\Http\Controllers;

use App\Action\Inventory\CreateInventoryUsage;
use App\Http\Requests\InventoryUsageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryItemUsageController extends Controller
{
    public function createUsage(InventoryUsageRequest $request, CreateInventoryUsage $createInventoryUsage): JsonResponse
    {
        return response()->json($createInventoryUsage($request->validated()));
    }
}
