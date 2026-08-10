<?php

namespace App\Support;

use App\Models\School;

class ModuleAccess
{
    public static function enabled(?School $school, string $code): bool
    {
        if (! $school) {
            return false;
        }

        if ($school->relationLoaded('modules')) {
            $module = $school->modules->firstWhere('code', $code);

            return $module ? (bool) $module->pivot->is_enabled && (bool) $module->is_active : true;
        }

        $module = $school->modules()
            ->where('code', $code)
            ->where('modules.is_active', true)
            ->first();

        return $module ? (bool) $module->pivot->is_enabled : true;
    }

    public static function anyEnabled(?School $school, array $codes): bool
    {
        foreach ($codes as $code) {
            if (self::enabled($school, $code)) {
                return true;
            }
        }

        return false;
    }
}
