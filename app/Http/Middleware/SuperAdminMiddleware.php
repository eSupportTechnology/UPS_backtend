<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Access denied. Super Admin role required.',
            'code' => Response::HTTP_FORBIDDEN
        ], Response::HTTP_FORBIDDEN);
    }
}
