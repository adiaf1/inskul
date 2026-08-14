<?php

namespace App\Http\Middleware;

use App\Models\SchoolDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSchoolDomain
{
    public const REQUEST_KEY = 'domain_school';
    public const SESSION_KEY = 'domain_school_id';

    public function handle(Request $request, Closure $next): Response
    {
        $host = SchoolDomain::normalizeDomain($request->getHost());
        $subdomain = SchoolDomain::subdomainFromHost($host);

        if ($host === '' || $subdomain === '') {
            return $next($request);
        }

        $schoolDomain = SchoolDomain::query()
            ->with('school')
            ->where(function ($query) use ($host, $subdomain) {
                $query->where('domain', $subdomain)
                    ->orWhere('domain', $host);
            })
            ->where('status', 'active')
            ->whereHas('school', fn ($query) => $query->where('status', 'active'))
            ->first();

        if (! $schoolDomain) {
            $request->session()->forget(self::SESSION_KEY);

            return $next($request);
        }

        $request->attributes->set(self::REQUEST_KEY, $schoolDomain->school);
        $request->session()->put(self::SESSION_KEY, $schoolDomain->school_id);

        return $next($request);
    }
}
