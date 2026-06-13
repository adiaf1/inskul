<?php

namespace App\Http\Middleware;

use App\Support\EffectiveAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || EffectiveAccess::role($request) !== 'school_admin') {
            return $next($request);
        }

        $school = EffectiveAccess::school($request);

        if (! $school || $school->onboarding_completed_at || $request->routeIs('school-onboarding.*')) {
            return $next($request);
        }

        return redirect()->route('school-onboarding.edit');
    }
}
