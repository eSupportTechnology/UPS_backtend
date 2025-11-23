<?php

namespace App\Http\Controllers;

use App\Action\AMC\ActivateAMCContract;
use App\Action\AMC\CreateAMCContract;
use App\Action\AMC\DeactivateAMCContract;
use App\Action\AMC\DeleteAMCContract;
use App\Action\AMC\ExportAMCContractsExcel;
use App\Action\AMC\ExportAMCContractsPdf;
use App\Action\AMC\GenerateAMCContractReport;
use App\Action\AMC\GetAllContracts;
use App\Action\AMC\UpdateAMCContract;
use App\Http\Requests\AMC\AMCContractRequest;
use App\Http\Requests\AMC\AMCReportRequest;
use App\Http\Requests\AMC\GetAllContractsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function activateAMCContract(string $id, ActivateAMCContract $action): JsonResponse
    {
        return response()->json($action($id));
    }

    public function deactivateAMCContract(string $id, DeactivateAMCContract $action): JsonResponse
    {
        return response()->json($action($id));
    }

    public function exportExcel(GetAllContractsRequest $request, ExportAMCContractsExcel $action): BinaryFileResponse
    {
        return $action($request->validated());
    }

    public function exportPdf(GetAllContractsRequest $request, ExportAMCContractsPdf $action): Response
    {
        return $action($request->validated());
    }

    public function generateReport(AMCReportRequest $request, GenerateAMCContractReport $action): JsonResponse
    {
        return response()->json($action($request->validated()));
    }
}
