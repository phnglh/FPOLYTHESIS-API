<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            if (Auth::user()->role !== $role) {
                return response()->json([
                    'message' => 'Unauthorized action',
                    'user_role' => Auth::user()->role,
                    'required_role' => $role,
                ], 403);
            }

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
