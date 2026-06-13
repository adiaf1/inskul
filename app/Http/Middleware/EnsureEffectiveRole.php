<?php

namespace App\Http\Middleware;

use App\Support\EffectiveAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEffectiveRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        if ($user->hasRole('super_admin') && in_array(EffectiveAccess::role($request), $roles, true)) {
            return $next($request);
        }

        abort(403);
    }
}
