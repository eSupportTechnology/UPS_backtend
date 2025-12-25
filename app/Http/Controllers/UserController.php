<?php

namespace App\Http\Controllers;

use App\Action\User\ActivateUser;
use App\Action\User\CreateTechnician;
use App\Action\User\DeactivateUser;
use App\Action\User\DeleteTechnician;
use App\Action\User\DeleteUser;
use App\Action\User\GetActiveCustomers;
use App\Action\User\GetAllTechnicianUsers;
use App\Action\User\GetAllUsers;
use App\Action\User\UpdateTechnician;
use App\Action\User\UpdateUser;
use App\Http\Requests\User\GetAllUsersRequest;
use App\Http\Requests\User\TechnicianCreateRequest;
use App\Http\Requests\User\TechnicianUpdateRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function getAllUsers(GetAllUsersRequest $request, GetAllUsers $getAllUsers): JsonResponse
    {
        $result = $getAllUsers($request->validated());

        return response()->json($result);
    }

    public function updateUser(string $id, UserUpdateRequest $request, UpdateUser $updateUser): JsonResponse
    {
        return response()->json($updateUser($id, $request->validated()));
    }

    public function deleteUser(string $id, DeleteUser $deleteUser): JsonResponse
    {
        return response()->json($deleteUser($id));
    }
    public function activateUser(string $id, ActivateUser $activateUser): JsonResponse
    {
        return response()->json($activateUser($id));
    }

    public function deactivateUser(string $id, DeactivateUser $deactivateUser): JsonResponse
    {
        return response()->json($deactivateUser($id));
    }
    public function getActiveCustomers(GetActiveCustomers $getActiveCustomers): JsonResponse
    {
        $result = $getActiveCustomers();

        return response()->json($result);
    }

    public function getAllTechnicianUsers(GetAllTechnicianUsers $getAllTechnicianUsers): JsonResponse
    {
        $filters = request()->only(['technician_type']);
        $result = $getAllTechnicianUsers($filters);
        return response()->json($result);
    }

    public function createTechnician(TechnicianCreateRequest $request, CreateTechnician $createTechnician): JsonResponse
    {
        return response()->json($createTechnician($request->validated()));
    }

    public function getTechnician(string $id): JsonResponse
    {
        try {
            $technician = User::where('role_as', User::ROLE_TECHNICIAN)
                ->select(
                    'id',
                    'name',
                    'email',
                    'phone',
                    'address',
                    'technician_type',
                    'employment_type',
                    'profile_image',
                    'specialization',
                    'is_active',
                    'created_at'
                )
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $technician
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Technician not found'
            ], 404);
        }
    }

    public function updateTechnician(string $id, TechnicianUpdateRequest $request, UpdateTechnician $updateTechnician): JsonResponse
    {
        return response()->json($updateTechnician($id, $request->validated()));
    }

    public function deleteTechnician(string $id, DeleteTechnician $deleteTechnician): JsonResponse
    {
        return response()->json($deleteTechnician($id));
    }
}
