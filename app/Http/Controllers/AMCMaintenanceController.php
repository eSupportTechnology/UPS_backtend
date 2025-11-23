<?php

namespace App\Http\Controllers;

use App\Action\AMC\AssignMaintenance;
use App\Http\Requests\AssignMaintenanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Action\AMCMaintenance\GetMaintenancesByAssignedTo;
use App\Http\Requests\AMCMaintenance\GetMaintenancesByAssignedToRequest;


class AMCMaintenanceController extends Controller
{
    public function assignMaintenance(AssignMaintenanceRequest $request, AssignMaintenance $assignMaintenance): JsonResponse
    {
        return response()->json($assignMaintenance($request->validated()));
    }
    public function getMaintenancesByAssignedTo(
        string $assigned_to,
        GetMaintenancesByAssignedToRequest $request,
        GetMaintenancesByAssignedTo $getMaintenancesByAssignedTo
    ): JsonResponse {
        $result = $getMaintenancesByAssignedTo($assigned_to, $request->validated());
        return response()->json($result);
    }
}
