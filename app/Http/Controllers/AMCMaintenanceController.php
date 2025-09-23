<?php

namespace App\Http\Controllers;

use App\Action\AMC\AssignMaintenance;
use App\Http\Requests\AssignMaintenanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AMCMaintenanceController extends Controller
{
    public function assignMaintenance(AssignMaintenanceRequest $request, AssignMaintenance $assignMaintenance): JsonResponse
    {
        return response()->json($assignMaintenance($request->validated()));
    }
}
