<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /** Allow the request only when the authenticated user's role is in the route list. */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        // Return JSON 403 so the Vue client can display an authorization message.
        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'You are not authorized to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
