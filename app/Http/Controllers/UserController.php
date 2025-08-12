<?php

namespace App\Http\Controllers;

use App\Action\User\ActivateUser;
use App\Action\User\DeactivateUser;
use App\Action\User\DeleteUser;
use App\Action\User\GetActiveCustomers;
use App\Action\User\GetAllTechnicianUsers;
use App\Action\User\GetAllUsers;
use App\Action\User\UpdateUser;
use App\Http\Requests\User\GetAllUsersRequest;
use App\Http\Requests\User\UserUpdateRequest;
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
        $result = $getAllTechnicianUsers();
        return response()->json($result);
    }
}
