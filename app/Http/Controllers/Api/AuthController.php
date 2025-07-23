<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\UserCredentialsMail;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_as' => $request->role_as,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            $token = $user->createToken(
                'maintenance_system_token',
                $this->getTokenAbilities($user->role_as),
                now()->addDays(7)
            )->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => '7 days'
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account is inactive. Contact administrator.'
                ], 403);
            }

            $user->tokens()->delete();

            $token = $user->createToken(
                'maintenance_system_token',
                $this->getTokenAbilities($user->role_as),
                now()->addDays(7)
            )->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => '7 days'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user) {
                $user->currentAccessToken()->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Logout successful'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleName(),
            'role_as' => $user->role_as,
            'phone' => $user->phone,
            'address' => $user->address,
            'is_active' => $user->is_active,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    private function getTokenAbilities(int $role): array
    {
        return match($role) {
            User::ROLE_SUPER_ADMIN => ['*'],
            User::ROLE_ADMIN => ['admin:*', 'operator:*', 'technician:*', 'customer:*'],
            User::ROLE_OPERATOR => ['operator:*', 'technician:*', 'customer:*'],
            User::ROLE_TECHNICIAN => ['technician:*', 'customer:*'],
            User::ROLE_CUSTOMER => ['customer:*'],
            default => ['customer:*'],
        };
    }

    public function createUserWithAutoPassword(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role_as' => ['required', 'integer', 'in:1,2,3,4,5'],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            $password = Str::random(10);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'role_as' => $request->role_as,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            Mail::to($user->email)->send(new UserCredentialsMail($user, $password));

            return response()->json([
                'status' => 'success',
                'message' => 'User created and credentials sent via email',
                'data' => $this->formatUserData($user)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
