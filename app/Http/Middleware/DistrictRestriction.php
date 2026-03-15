<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DistrictRestriction
{
    /**
     * Ensure sub-admins can only access data for their assigned district.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isSubAdmin()) {
            // Auto-scope district_id in request to sub-admin's district
            $request->merge(['district_id' => $user->district_id]);
        }

        return $next($request);
    }
}
