<?php

namespace App\Http\Controllers;

use App\Action\AMC\CreateAMCContract;
use App\Action\AMC\DeleteAMCContract;
use App\Action\AMC\GetAllContracts;
use App\Action\AMC\UpdateAMCContract;
use App\Http\Requests\AMC\AMCContractRequest;
use App\Http\Requests\AMC\GetAllContractsRequest;
use Illuminate\Http\JsonResponse;

class AMCContractController extends Controller
{
    public function createContract(AMCContractRequest $request, CreateAMCContract $createAMCContract): JsonResponse
    {
        return response()->json($createAMCContract($request->validated()));
    }

    public function getAllContract(GetAllContractsRequest $request, GetAllContracts $getAllContracts): JsonResponse
    {
        $result = $getAllContracts($request->validated());
        return response()->json($result);
    }

    public function updateAMCContract(string $id, AMCContractRequest $request, UpdateAMCContract $action): JsonResponse
    {
        return response()->json($action($id, $request->validated()));
    }

    public function deleteAMCContract(string $id, DeleteAMCContract $action): JsonResponse
    {
        return response()->json($action($id));
    }
}
