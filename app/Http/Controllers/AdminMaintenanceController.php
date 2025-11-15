<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMaintenanceController extends Controller
{
    public function index(): JsonResponse
    {
        $admins = User::where('role', 'admin')->paginate(20);
        return response()->json($admins);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $data['role'] = 'admin';
        $data['password'] = bcrypt($data['password']);

        $admin = User::create($data);

        return response()->json($admin, 201);
    }

    public function show(string $id): JsonResponse
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        return response()->json($admin);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        $data = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $admin->id,
            'password' => 'sometimes|string|min:6',
        ]);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $admin->update($data);

        return response()->json($admin);
    }

    public function destroy(string $id): JsonResponse
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Admin deleted']);
    }

    public function activate(string $id): JsonResponse
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        $admin->update(['is_active' => true]);

        return response()->json(['message' => 'Admin activated']);
    }

    public function deactivate(string $id): JsonResponse
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        $admin->update(['is_active' => false]);

        return response()->json(['message' => 'Admin deactivated']);
    }
}
