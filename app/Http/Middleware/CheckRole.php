<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = $request->user();

        // Check if user is active
        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated'
            ], 403);
        }

        // Check role
        if ($role === 'admin' && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        if ($role === 'vendedor' && !$user->isVendedor()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Vendedor role required.'
            ], 403);
        }

        return $next($request);
    }
}
