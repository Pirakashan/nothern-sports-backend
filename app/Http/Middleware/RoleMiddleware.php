<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Flatten roles if they were passed as a comma-separated string (e.g. role:admin,editor)
        $allRoles = [];
        foreach ($roles as $role) {
            $allRoles = array_merge($allRoles, explode(',', $role));
        }
        $allRoles = array_map('trim', $allRoles);

        if (!in_array($request->user()->role, $allRoles)) {
            return response()->json(['message' => 'Unauthorized. Insufficient role.'], 403);
        }

        return $next($request);
    }
}
