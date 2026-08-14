<?php

namespace App\Support;

use App\Models\School;
use App\Models\User;
use App\Http\Middleware\ResolveSchoolDomain;
use Illuminate\Http\Request;

class EffectiveAccess
{
    public const SESSION_KEY = 'view_as';

    public static function active(Request $request): bool
    {
        return (bool) $request->session()->get(self::SESSION_KEY.'.active', false)
            && $request->user()?->hasRole('super_admin');
    }

    public static function role(Request $request): ?string
    {
        if (self::active($request)) {
            return $request->session()->get(self::SESSION_KEY.'.role');
        }

        return $request->user()?->roles()->value('name');
    }

    public static function school(Request $request): ?School
    {
        if (self::active($request)) {
            return School::query()
                ->whereKey($request->session()->get(self::SESSION_KEY.'.school_id'))
                ->where('status', 'active')
                ->first();
        }

        $domainSchool = $request->attributes->get(ResolveSchoolDomain::REQUEST_KEY);

        if ($domainSchool instanceof School) {
            if (! $request->user()) {
                return $domainSchool;
            }

            $hasAccess = $request->user()
                ->schools()
                ->whereKey($domainSchool->id)
                ->wherePivot('status', 'active')
                ->where('schools.status', 'active')
                ->exists();

            return $hasAccess ? $domainSchool : null;
        }

        return $request->user()
            ?->schools()
            ->wherePivot('status', 'active')
            ->where('schools.status', 'active')
            ->first();
    }

    public static function user(Request $request): ?User
    {
        if (self::active($request) && $request->session()->has(self::SESSION_KEY.'.user_id')) {
            return User::find($request->session()->get(self::SESSION_KEY.'.user_id'));
        }

        return $request->user();
    }

    public static function payload(Request $request): array
    {
        return $request->session()->get(self::SESSION_KEY, []);
    }
}
