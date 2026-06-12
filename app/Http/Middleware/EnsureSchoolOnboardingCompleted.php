<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('school_admin')) {
            return $next($request);
        }

        $school = $user->schools()
            ->wherePivot('status', 'active')
            ->where('schools.status', 'active')
            ->first();

        if (! $school || $school->onboarding_completed_at || $request->routeIs('school-onboarding.*')) {
            return $next($request);
        }

        return redirect()->route('school-onboarding.edit');
    }
}
