<?php

namespace App\Http\Controllers;

use App\Action\Branch\ActivateBranch;
use App\Action\Branch\CreateBranch;
use App\Action\Branch\DeactivateBranch;
use App\Action\Branch\DeleteBranch;
use App\Action\Branch\GetAllBranches;
use App\Action\Branch\UpdateBranch;
use App\Http\Requests\Branch\BranchRequest;
use App\Http\Requests\Branch\GetAllBranchesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function getAllBranches(GetAllBranchesRequest $request, GetAllBranches $getAllBranches): JsonResponse
    {
        $result = $getAllBranches($request->validated());

        return response()->json($result);
    }

    public function createBranch(BranchRequest $request, CreateBranch $createBranch): JsonResponse
    {
        return response()->json($createBranch($request->validated()));
    }

    public function updateBranch(string $id, BranchRequest $request, UpdateBranch $updateBranch): JsonResponse
    {
        return response()->json($updateBranch($id, $request->validated()));
    }

    public function deleteBranch(string $id, DeleteBranch $deleteBranch): JsonResponse
    {
        return response()->json($deleteBranch($id));
    }


    public function activateBranch(string $id, ActivateBranch $activateBranch): JsonResponse
    {
        return response()->json($activateBranch($id));
    }

    public function deactivateBranch(string $id, DeactivateBranch $deactivateBranch): JsonResponse
    {
        return response()->json($deactivateBranch($id));
    }
}
