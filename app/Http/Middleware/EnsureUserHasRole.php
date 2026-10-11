<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(
        Request $request,
        Closure $next,
        string $role
    ): Response {
        $user = $request->user();

        if (!$user || $user->role !== $role) {
            abort(403, 'Unauthorized access.');
        }

        if (!$user->is_verified || !$user->is_active) {
            abort(403, 'Your account is not verified or is inactive.');
        }

        return $next($request);
    }
}
