<?php

namespace App\Http\Middleware;

use App\Support\EffectiveAccess;
use App\Support\ModuleAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolModuleEnabled
{
    public function handle(Request $request, Closure $next, string ...$codes): Response
    {
        $school = EffectiveAccess::school($request);

        if (! ModuleAccess::anyEnabled($school, $codes)) {
            abort(403, 'Modul ini belum aktif untuk sekolah.');
        }

        return $next($request);
    }
}
