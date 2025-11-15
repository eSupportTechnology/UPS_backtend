<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperatorMaintenanceController extends Controller
{
    public function index(): JsonResponse
    {
        $operators = User::where('role', 'operator')->paginate(20);
        return response()->json($operators);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $data['role'] = 'operator';
        $data['password'] = bcrypt($data['password']);

        $operator = User::create($data);

        return response()->json($operator, 201);
    }

    public function show(string $id): JsonResponse
    {
        $operator = User::where('role', 'operator')->findOrFail($id);
        return response()->json($operator);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $operator = User::where('role', 'operator')->findOrFail($id);

        $data = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $operator->id,
            'password' => 'sometimes|string|min:6',
        ]);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $operator->update($data);

        return response()->json($operator);
    }

    public function destroy(string $id): JsonResponse
    {
        $operator = User::where('role', 'operator')->findOrFail($id);
        $operator->delete();

        return response()->json(['message' => 'Operator deleted']);
    }

    public function activate(string $id): JsonResponse
    {
        $operator = User::where('role', 'operator')->findOrFail($id);
        $operator->update(['is_active' => true]);

        return response()->json(['message' => 'Operator activated']);
    }

    public function deactivate(string $id): JsonResponse
    {
        $operator = User::where('role', 'operator')->findOrFail($id);
        $operator->update(['is_active' => false]);

        return response()->json(['message' => 'Operator deactivated']);
    }
}
