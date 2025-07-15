<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (auth()->check() && $user->hasAnyRole([User::ROLE_ADMIN])) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Access denied. Admin role required.',
            'code' => Response::HTTP_FORBIDDEN
        ], Response::HTTP_FORBIDDEN);
    }
}
